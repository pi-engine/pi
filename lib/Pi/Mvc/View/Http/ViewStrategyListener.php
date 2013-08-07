<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\View\Http;

use Pi;
use Pi\Feed\Model as FeedDataModel;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Model\FeedModel;
use Zend\Stdlib\ResponseInterface as Response;

/**
 * Global view strategy listener
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ViewStrategyListener extends AbstractListenerAggregate
{
    /**
     * Request accept type
     * @var string
     */
    protected $type;

    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $sharedEvents = $events->getSharedManager();

        // Detect request type and disable debug in case necessary
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_BOOTSTRAP,
            array($this, 'prepareRequestType'),
            99999
         );

        // Prepare root ViewModel for MvcEvent
        // Must be triggered before ViewManager
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_BOOTSTRAP,
            array($this, 'prepareRootModel'),
            20000
        );

        // Preload variables from system config for theme
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_ROUTE,
            array($this, 'initThemeAssemble'),
            10000
        );

        // Canonize ViewModel for action
        $sharedEvents->attach(
            'Zend\Stdlib\DispatchableInterface',
            MvcEvent::EVENT_DISPATCH,
            array($this, 'canonizeActionResult'),
            -70
        );

        // Inject ViewModel, should be performed
        // prior to Zend\Mvc\View\Http\InjectTemplateListener::injectTemplate()
        // whose priority is -90
        // and skip following error status:
        // NotFound handled by:
        // Zend\Mvc\View\Http\RouteNotFoundStrategy::prepareNotFoundViewModel()
        // whose priority is -90
        // Error handled by:
        // Pi\Mvc\View\Http\ErrorStrategy::prepareErrorViewModel()
        // whose priority is --85
        $sharedEvents->attach(
            'Zend\Stdlib\DispatchableInterface',
            MvcEvent::EVENT_DISPATCH,
            array($this, 'injectTemplate'),
            -89
        );

        // Render head metas for theme
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            array($this, 'renderThemeAssemble'),
            10000
        );
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'renderThemeAssemble'),
            10000
        );

        // Canonize ViewModel for error/exception
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            array($this, 'canonizeErrorResult'),
            10
        );

        // Canonize theme layout if necessary
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            array($this, 'canonizeThemeLayout'),
            5
        );

        // Complete meta assemble for theme
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_FINISH,
            array($this, 'completeThemeAssemble'),
            10000
        );
    }

    /**
     * Set request type explicitly
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get request type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Detect request type and set debug mode
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareRequestType(MvcEvent $e)
    {
        // Detect request type
        $type = $this->detectType($e);

        // Disable error debugging for AJAX and Flash
       if ($type) {
            Pi::service('log')->mute();
        }
    }

    /**
     * Prepare root view model for MvcEvent
     *
     * @param MvcEvent $e
     * @return void
     */
    public function prepareRootModel(MvcEvent $e)
    {
        if ('json' == $this->type) {
            $e->setViewModel(new JsonModel);
        } elseif ('feed' == $this->type) {
            $e->setViewModel(new FeedModel);
        } else {
            $e->setViewModel(new ViewModel);
        }
    }

    /**
     * Inspect the result, and cast it to a ViewModel
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function canonizeActionResult(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        // MVC controller
        $controller = $e->getTarget();
        // Controller view
        $controllerVew = $controller->view();
        // ViewModel generated by controller
        $viewModel = null;

        // Cast controller ViewModel
        if ($controllerVew->hasViewModel()) {
            $viewModel = $controllerVew->getViewModel();
            $template = $viewModel->getTemplate();

            // Controller ViewModel is as the main model if if if is specified
            // with template, MvcEvent result is converted to variables of
            // the ViewModel
            if ($viewModel instanceof ViewModel
                && !$viewModel instanceof JsonModel
                && !$viewModel instanceof FeedModel
                && $template
                && '__NULL__' != $template
            ) {
                $variables = array();
                $options = array();
                if ($result instanceof ViewModel) {
                    $variables = $result->getVariables();
                    $options = $result->getOptions();
                } elseif ($result instanceof FeedDataModel) {
                    $variables = (array) $result;
                    $options = array('feed_type' => $result->getType());
                } elseif (ArrayUtils::hasStringKeys($result, true)) {
                    $variables = array_merge_recursive($variables, $result);
                }
                $viewModel->setVariables($variables);
                $viewModel->setOptions($options);
                $e->setResult($viewModel);

                return;
            }
        }

        // Cast controller view model to result viewmodel
        switch ($this->type) {
            // For Feed
            case 'feed':
                if ($result instanceof FeedModel) {
                    $model = $result;
                } else {
                    $variables = array();
                    $options = array();
                    if ($result instanceof ViewModel) {
                        $variables = $result->getVariables();
                        $options = $result->getOptions();
                    } elseif ($result instanceof FeedDataModel) {
                        $variables = (array) $result;
                        $options = array('feed_type' => $result->getType());
                    } else {
                        if ($viewModel) {
                            $variables = $viewModel->getVariables();
                            $options = $viewModel->getOptions();
                        }
                        if (ArrayUtils::hasStringKeys($result, true)) {
                            $variables = array_merge_recursive(
                                $variables,
                                $result
                            );
                        }
                    }
                    $model = new FeedModel($variables, $options);
                }
                break;
            // For Json
            case 'json':
                if ($result instanceof JsonModel) {
                    $model = $result;
                } else {
                    $variables = array();
                    $options = array();
                    if ($result instanceof ViewModel) {
                        $variables = $result->getVariables();
                        $options = $result->getOptions();
                    } else {
                        if ($viewModel) {
                            $variables = $viewModel->getVariables();
                            $options = $viewModel->getOptions();
                        }
                        if (ArrayUtils::hasStringKeys($result, true)) {
                            $variables = array_merge_recursive(
                                $variables,
                                $result
                            );
                        }
                    }
                    $model = new JsonModel($variables, $options);
                }
                break;

            // For AJAX/Flash
            case 'ajax':
            // MISC
            default:
                if ($viewModel) {
                    $model = $viewModel;
                } elseif ($result instanceof ViewModel) {
                    $model = $result;
                    $result = null;
                } else {
                    $model = new ViewModel;
                }

                if (null !== $result) {
                    $template = $model->getTemplate();
                    if ($this->type
                        && (!$template || '__NULL_' == $template)
                    ) {
                        $model->setVariable('content', is_scalar($result)
                            ? $result : json_encode($result));
                    } elseif (ArrayUtils::hasStringKeys($result, true)) {
                        $model->setVariables($result);
                    } elseif (is_scalar($result)) {
                        $model->setVariable('content', $result);
                    } else {
                        $model->setVariable('content', json_encode($result));
                    }
                }
                if ($this->type) {
                    $model->terminate(true);
                }
                break;
        }
        $e->setResult($model);
    }

    /**
     * Inspect the result for erroneous action of JSON/AJAX/Feed
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function canonizeErrorResult(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }
        if (!$this->type) {
            return;
        }

        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        if ($statusCode < 400) {
            return;
        }

        // Cast controller view model to result viewmodel
        switch ($this->type) {
            // For Feed
            case 'feed':
                if ($result instanceof FeedModel) {
                    $model = $result;
                    $model->setTemplate('');
                    $model->clearChildren();
                } else {
                    $variables = array();
                    $options = array();
                    if ($result instanceof ViewModel) {
                        $variables = $result->getVariables();
                        $options = $result->getOptions();
                    } elseif ($result instanceof FeedDataModel) {
                        $variables = (array) $result;
                        $options = array('feed_type' => $result->getType());
                    }
                    $model = new FeedModel($variables, $options);
                }
                break;
            // For Json
            case 'json':
                if ($result instanceof JsonModel) {
                    $model = $result;
                    $model->setTemplate('');
                    $model->clearChildren();
                } else {
                    $variables = array();
                    $options = array();
                    if ($result instanceof ViewModel) {
                        $variables = $result->getVariables();
                        $options = $result->getOptions();
                    }
                    $model = new JsonModel($variables, $options);
                }
                break;

            // For AJAX/Flash
            case 'ajax':
            // MISC
            default:
                if ($result instanceof ViewModel) {
                    $model = $result;
                    $model->setTemplate('');
                    $model->clearChildren();
                    $result = null;
                } else {
                    $model = new ViewModel;
                }
                $model->terminate(true);

                break;
        }
        $e->setViewModel($model);
    }

    /**
     * Inject a template into the ViewModel if none present,
     * to skip Zend native InjectTemplateListener
     *
     * Template is derived from the controller found in the route match, and,
     * optionally, the action, if present.
     *
     * @see Pi\Mvc\Controller\Plugin\View::setTemplate()
     * @param  MvcEvent $e
     * @return void
     */
    public function injectTemplate(MvcEvent $e)
    {
        $model = $e->getResult();
        if (!$model instanceof ViewModel) {
            return;
        }
        $template = $model->getTemplate();
        // Set pseudo template for JsonModel/FeedModel
        if ($model instanceof JsonModel || $model instanceof FeedModel) {
            if (!$template) {
                $model->setTemplate('__NULL__');
            }
            return;
        }
        // Set pseudo template for AJAX if no template is set yet
        if ('ajax' == $this->type && !$template) {
            $model->setTemplate('__NULL__');
            return;
        }

        // Preload  module/controller/action variables
        // for regular theme template
        $routeMatch = $e->getRouteMatch();
        if ('__NULL__' != $template) {
            $model->setVariables(array(
                'module'        => $routeMatch->getParam('module'),
                'controller'    => $routeMatch->getParam('controller'),
                'action'        => $routeMatch->getParam('action'),
            ));
        }
        if ($template || $e->isError()) {
            return;
        }

        // Set template for regular module-controller-action request:
        // module:section/controller-action
        $engine = $e->getApplication()->getEngine();
        $template = sprintf(
            '%s:%s/%s-%s',
            $routeMatch->getParam('module'),
            $engine->section(),
            $routeMatch->getParam('controller'),
            $routeMatch->getParam('action')
        );
        $model->setTemplate($template);
    }

    /**
     * Canonize layout template for theme
     *
     * @param MvcEvent $e
     * @return void
     */
    public function canonizeThemeLayout(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        $viewModel = $e->getViewModel();
        // Skip for JsonModel/FeedModel which do not need template
        if (!$viewModel instanceof ViewModel
            || $viewModel instanceof JsonModel
            || $viewModel instanceof FeedMode
        ) {
            return;
        }

        // Fetch service configuration
        $config     = $e->getApplication()->getServiceManager()->get('Config');
        $viewConfig = $config['view_manager'];
        // Specify AJAX layout
        if ('ajax' == $this->type) {
            $viewModel->setTemplate(isset($viewConfig['layout_ajax'])
                ? $viewConfig['layout_ajax'] : 'layout-content');
        // Specify error page layout
        } elseif ($e->isError()) {
            $viewModel->setTemplate(isset($viewConfig['layout_error'])
                ? $viewConfig['layout_error'] : 'layout-style');
        }
    }

    /**
     * Initialize assemble with config meta
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function initThemeAssemble(MvcEvent $e)
    {
        if ($this->skipThemeAssemble($e)) {
            return;
        }

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()
            ->get('ViewRenderer');
        $viewRenderer->assemble()->initStrategy();
    }

    /**
     * Canonize head title by appending site name and/or slogan
     *
     * @param MvcEvent $e
     * @return void
     */
    public function renderThemeAssemble(MvcEvent $e)
    {
        if ($this->skipThemeAssemble($e)) {
            return;
        }

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()
            ->get('ViewRenderer');
        $viewRenderer->assemble()->renderStrategy();
    }

    /**
     * Assemble meta contents
     *
     * @param MvcEvent $e
     * @return void
     */
    public function completeThemeAssemble(MvcEvent $e)
    {
        if ($this->skipThemeAssemble($e)) {
            return;
        }

        // Set response headers for language and charset
        $response = $e->getResponse();
        $response->getHeaders()->addHeaders(array(
            'content-type'      => sprintf('text/html; charset=%s',
                Pi::service('i18n')->charset),
            'content-language'  => Pi::service('i18n')->locale,
        ));

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()
            ->get('ViewRenderer');
        $content = $response->getContent();
        $content = $viewRenderer->assemble()->completeStrategy($content);
        $response->setContent($content);
    }

    /**
     * Detect request type according to HTTP request and headers
     *
     * @param MvcEvent $e
     * @return string
     */
    protected function detectType(MvcEvent $e)
    {
        // Skip if already set
        if (null !== $this->type) {
            return $this->type;
        }
        // Default as regular request
        $this->type = '';


        $request = $e->getRequest();
        // AJAX
        if ($request->isXmlHttpRequest()) {
            $this->type = 'ajax';
        // Flash
        } elseif ($request->isFlashRequest()) {
            $this->type = 'flash';
        }

        $headers = $request->getHeaders();
        if (!$headers->has('accept')) {
            return $this->type;
        }
        $accept  = $headers->get('Accept');

        // Json
        if (($match = $accept->match(
            'application/json, application/javascript'
        )) != false) {
            $typeString = $match->getTypeString();
            if ('application/json' == $typeString
                || 'application/javascript' == $typeString
            ) {
                $this->type = 'json';
            }
        // Feed
        } elseif (($match = $accept->match(
            'application/rss+xml, application/atom+xml'
        )) != false) {
            $typeString = $match->getTypeString();
            if ('application/rss+xml' == $typeString
                || 'application/atom+xml' == $typeString
            ) {
                $this->type = 'feed';
            }
        }

        return $this->type;
    }

    /**
     * Detect if theme assemble can be skipped
     *
     * @param MvcEvent $e
     * @return bool
     */
    protected function skipThemeAssemble(MvcEvent $e)
    {
        // Skip for AJAX/Flash/Json/Feed
        if ($this->type) {
            return true;
        }
        // Skip if response is already generated
        if ($e->getResult() instanceof Response) {
            return true;
        }

        // Skip if root model has no template
        $viewModel = $e->getViewModel();
        if (!$viewModel instanceof ViewModel || !$viewModel->getTemplate()) {
            return true;
        }

        return false;
    }
}
