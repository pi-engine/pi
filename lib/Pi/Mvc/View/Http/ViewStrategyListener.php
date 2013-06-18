<?php
/**
 * Global View Strategy Listener
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Mvc
 * @subpackage      View
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

class ViewStrategyListener extends AbstractListenerAggregate
{
    /**
     * Request accept type
     *
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
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareRequestType'),  99999);

        // Prepare root ViewModel for MvcEvent
        // Must be triggered before ViewManager
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareRootModel'), 20000);

        // Preload variables from system config for theme
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'initThemeAssemble'), 10000);

        // Canonize ViewModel for action
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'canonizeActionResult'), -70);

        // Inject ViewModel, should be performed before injectTemplateListener
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'injectTemplate'), -85);

        // Render head metas for theme
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'renderThemeAssemble'), 10000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'renderThemeAssemble'), 10000);

        // Set theme layout if necessary
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'setThemeLayout'), 1000);

        // Complete meta assemble for theme
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'completeThemeAssemble'), 10000);
    }

    public function setType($type)
    {
        return $this->type = $type;
    }

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
            Pi::service('log')->active(false);
            /*
            // Not implemented yet
            if (Pi::service('log')->debugger()) {
                Pi::service('log')->debugger()->disable();
            }
            */
        }
    }

    /**
     * Prepare root view model for MvcEvent
     *
     * @param MvcEvent $e
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

        $controller = $e->getTarget();
        $controllerVew = $controller->view();
        $viewModel = null;
        if ($controllerVew->hasViewModel()) {
            $viewModel = $controllerVew->getViewModel();
            $template = $viewModel->getTemplate();
            // If controller ViewModel is specified with template, MvcEvent result is converted to variables of the ViewModel
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

        switch ($this->type) {
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
                        if ($ViewModel) {
                            $variables = $viewModel->getVariables();
                            $options = $viewModel->getOptions();
                        }
                        if (ArrayUtils::hasStringKeys($result, true)) {
                            $variables = array_merge_recursive($variables, $result);
                        }
                    }
                    $model = new FeedModel($variables, $options);
                }
                break;
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
                            $variables = array_merge_recursive($variables, $result);
                        }
                    }
                    $model = new JsonModel($variables, $options);
                }
                break;
            case 'ajax':
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
                    if ('ajax' == $this->type && (!$template || '__NULL_' == $template)) {
                        $model->setVariable('content', is_scalar($result) ? $result : json_encode($result));
                    } elseif (ArrayUtils::hasStringKeys($result, true)) {
                        $model->setVariables($result);
                    } elseif (is_scalar($result)) {
                        $model->setVariable('content', $result);
                    } else {
                        $model->setVariable('content', json_encode($result));
                    }
                }
                if ('ajax' == $this->type) {
                    $model->terminate(true);
                }
                break;
        }
        $e->setResult($model);
    }

    /**
     * Inject a template into the ViewModel, if none present
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
        $template   = $model->getTemplate();
        if ($model instanceof JsonModel || $model instanceof FeedModel) {
            if (!$template) {
                $model->setTemplate('__NULL__');
            }
            return;
        }
        if ('ajax' == $this->type && !$template) {
            $model->setTemplate('__NULL__');
            return;
        }

        $routeMatch = $e->getRouteMatch();
        if ('__NULL__' != $template) {
            $model->setVariables(array(
                'module'        => $routeMatch->getParam('module'),
                'controller'    => $routeMatch->getParam('controller'),
                'action'        => $routeMatch->getParam('action'),
            ));
        }

        if ($template) {
            return;
        }
        $engine = $e->getApplication()->getEngine();
        $template = sprintf('%s:%s/%s-%s', $routeMatch->getParam('module'), $engine->section(), $routeMatch->getParam('controller'), $routeMatch->getParam('action'));
        $model->setTemplate($template);
    }

    public function setThemeLayout(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        $viewModel = $e->getViewModel();
        if (!$viewModel instanceof ViewModel || $viewModel instanceof JsonModel || $viewModel instanceof FeedModel) {
            return;
        }

        $config     = $e->getApplication()->getServiceManager()->get('Config');
        $viewConfig = $config['view_manager'];
        // Set up AJAX layout
        if ('ajax' == $this->type) {
            $viewModel->setTemplate(isset($viewConfig['layout_ajax']) ? $viewConfig['layout_ajax'] : 'layout-content');
        // Set error page layout
        } elseif ($e->isError()) {
            $viewModel->setTemplate(isset($viewConfig['layout_error']) ? $viewConfig['layout_error'] : 'layout-style');
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
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
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
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
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
            'content-type'      => sprintf('text/html; charset=%s', Pi::service('i18n')->charset),
            'content-language'  => Pi::service('i18n')->locale,
        ));

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
        $content = $response->getContent();
        $content = $viewRenderer->assemble()->completeStrategy($content);
        $response->setContent($content);
    }

    protected function detectType(MvcEvent $e)
    {
        if (null !== $this->type) {
            return $this->type;
        }
        $this->type = '';

        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            $this->type = 'ajax';
        } elseif ($request->isFlashRequest()) {
            $this->type = 'flash';
        }

        $headers = $request->getHeaders();
        if (!$headers->has('accept')) {
            return $this->type;
        }
        $accept  = $headers->get('Accept');

        // Json
        if (($match = $accept->match('application/json, application/javascript')) != false) {
            $typeString = $match->getTypeString();
            if ('application/json' == $typeString || 'application/javascript' == $typeString) {
                $this->type = 'json';
            }
        // Feed
        } elseif (($match = $accept->match('application/rss+xml, application/atom+xml')) != false) {
            $typeString = $match->getTypeString();
            if ('application/rss+xml' == $typeString || 'application/atom+xml' == $typeString) {
                $this->type = 'feed';
            }
        }

        return $this->type;
    }

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
