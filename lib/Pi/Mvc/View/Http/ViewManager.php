<?php
/**
 * View Manager
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
 * @since           3.0
 * @package         Pi\Mvc
 * @subpackage      View
 * @version         $Id$
 */

namespace Pi\Mvc\View\Http;

use Pi;

use Pi\View\Renderer\PhpRenderer as ViewPhpRenderer;
use Pi\View\Strategy\PhpRendererStrategy;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\ViewManager as ZendViewManager;
use Pi\Mvc\View\Http\CreateViewModelListener;
use Zend\Mvc\View\Http\InjectViewModelListener;
use Pi\Mvc\View\Http\InjectTemplateListener;

/**
 * Prepares the view layer
 *
 * Instantiates and configures all classes related to the view layer, including
 * the renderer (and its associated resolver(s) and helper manager), the view
 * object (and its associated rendering strategies), and the various MVC
 * strategies and listeners.
 *
 * Defines and manages the following services:
 *
 * - ViewHelperManager (also aliased to Zend\View\HelperPluginManager)
 * - ViewTemplateMapResolver (also aliased to Zend\View\Resolver\TemplateMapResolver)
 * - ViewTemplatePathStack (also aliased to Zend\View\Resolver\TemplatePathStack)
 * - ViewResolver (also aliased to Zend\View\Resolver\AggregateResolver and ResolverInterface)
 * - ViewRenderer (also aliased to Zend\View\Renderer\PhpRenderer and RendererInterface)
 * - ViewPhpRendererStrategy (also aliased to Zend\View\Strategy\PhpRendererStrategy)
 * - View (also aliased to Zend\View\View)
 * - DefaultRenderingStrategy (also aliased to Zend\Mvc\View\Http\DefaultRenderingStrategy)
 * - ExceptionStrategy (also aliased to Zend\Mvc\View\Http\ExceptionStrategy)
 * - RouteNotFoundStrategy (also aliased to Zend\Mvc\View\Http\RouteNotFoundStrategy and 404Strategy)
 * - ViewModel
 *
 * @see   Zend\Mvc\View\ViewManager
 */
class ViewManager extends ZendViewManager
{
    protected $deniedStrategy;

    /**
     * Prepares the view layer
     *
     * @param  $event
     * @return void
     */
    public function onBootstrap($event)
    {
        $application  = $event->getApplication();
        $services     = $application->getServiceManager();
        $config       = $services->get('Config');
        $events       = $application->getEventManager();
        $sharedEvents = $events->getSharedManager();

        $this->config   = isset($config['view_manager']) && (is_array($config['view_manager']) || $config['view_manager'] instanceof ArrayAccess)
                        ? $config['view_manager']
                        : array();
        $this->services = $services;
        $this->event    = $event;

        $routeNotFoundStrategy   = $this->getRouteNotFoundStrategy();
        $exceptionStrategy       = $this->getExceptionStrategy();
        $mvcRenderingStrategy    = $this->getMvcRenderingStrategy();
        $createViewModelListener = new CreateViewModelListener();
        $injectTemplateListener  = new InjectTemplateListener();
        $injectViewModelListener = new InjectViewModelListener();

        $this->registerMvcRenderingStrategies($events);
        /**#@+
         * Add demanded strategies in case they are not specified
         */
        $this->detectViewStrategies($createViewModelListener);
        /**#@-*/
        $this->registerViewStrategies();

        $events->attach($routeNotFoundStrategy);
        $events->attach($exceptionStrategy);
        $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($injectViewModelListener, 'injectViewModel'), -100);
        $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($injectViewModelListener, 'injectViewModel'), -100);
        $events->attach($mvcRenderingStrategy);

        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($createViewModelListener, 'createViewModelFromArray'), -80);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($routeNotFoundStrategy, 'prepareNotFoundViewModel'), -90);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($createViewModelListener, 'createViewModelFromNull'), -80);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($injectTemplateListener, 'injectTemplate'), -90);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($injectViewModelListener, 'injectViewModel'), -100);


        $deniedStrategy = $this->getDeniedStrategy();
        $events->attach($deniedStrategy);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($deniedStrategy, 'prepareDeniedViewModel'), -90);
    }

    /**
     * Instantiates and configures the renderer
     *
     * @return ViewPhpRenderer
     */
    public function getRenderer()
    {
        if ($this->renderer) {
            return $this->renderer;
        }

        $this->renderer = new ViewPhpRenderer;
        $this->renderer->setHelperPluginManager($this->getHelperManager());
        $this->renderer->setResolver($this->getResolver());

        $model       = $this->getViewModel();
        $modelHelper = $this->renderer->plugin('view_model');
        $modelHelper->setRoot($model);

        $this->services->setService('ViewRenderer', $this->renderer);
        $this->services->setAlias('Zend\View\Renderer\PhpRenderer', 'ViewRenderer');
        $this->services->setAlias('Zend\View\Renderer\RendererInterface', 'ViewRenderer');

        return $this->renderer;
    }

    /**
     * Instantiates and configures the renderer strategy for the view
     *
     * @return PhpRendererStrategy
     */
    public function getRendererStrategy()
    {
        if ($this->rendererStrategy) {
            return $this->rendererStrategy;
        }

        $this->rendererStrategy = new PhpRendererStrategy(
            $this->getRenderer()
        );

        $this->services->setService('ViewPhpRendererStrategy', $this->rendererStrategy);
        $this->services->setAlias('Zend\View\Strategy\PhpRendererStrategy', 'ViewPhpRendererStrategy');

        return $this->rendererStrategy;
    }

    /**
     * Get the layout template name
     *
     * @return string
     */
    public function getLayoutTemplate()
    {
        if (isset($this->config['layout'])) {
            $layout = $this->config['layout'];
        } else {
            $layout = sprintf('layout-%s', Pi::engine()->section());
        }
        return $layout;
    }

    /**
     * Get the error layout template name
     *
     * @return string
     */
    public function getLayoutError()
    {
        if (isset($this->config['layout_error'])) {
            $layout = $this->config['layout_error'];
        } else {
            $layout = 'layout-error';
        }
        return $layout;
    }

    /**
     * Instantiates and configures the default MVC rendering strategy
     *
     * @return DefaultRenderingStrategy
     */
    public function getMvcRenderingStrategy()
    {
        if ($this->mvcRenderingStrategy) {
            return $this->mvcRenderingStrategy;
        }

        $this->mvcRenderingStrategy = new DefaultRenderingStrategy($this->getView());
        $this->mvcRenderingStrategy->setLayoutTemplate($this->getLayoutTemplate());
        $this->mvcRenderingStrategy->setLayoutError($this->getLayoutError());

        $this->services->setService('DefaultRenderingStrategy', $this->mvcRenderingStrategy);
        $this->services->setAlias('Zend\Mvc\View\DefaultRenderingStrategy', 'DefaultRenderingStrategy');
        $this->services->setAlias('Zend\Mvc\View\Http\DefaultRenderingStrategy', 'DefaultRenderingStrategy');

        return $this->mvcRenderingStrategy;
    }

    /**
     * Instantiates and configures the "denied", or 401/403, strategy
     *
     * @return DeniedStrategy
     */
    public function getDeniedStrategy()
    {
        if ($this->deniedStrategy) {
            return $this->deniedStrategy;
        }

        $this->deniedStrategy = new DeniedStrategy();

        $deniedTemplate = 'denied';
        if (isset($this->config['denied_template'])) {
            $deniedTemplate = $this->config['denied_template'];
        }

        $this->deniedStrategy->setDeniedTemplate($deniedTemplate);

        $this->services->setService('DeniedStrategy', $this->deniedStrategy);
        $this->services->setAlias('Pi\Mvc\View\Http\DeniedFoundStrategy', 'DeniedStrategy');
        $this->services->setAlias('401Strategy', 'DeniedStrategy');

        return $this->deniedStrategy;
    }


    /**
     * Register additional view strategies on HTTP header detection
     *
     * @param CreateViewModelListener $listener
     * @return void
     */
    protected function detectViewStrategies(CreateViewModelListener $listener)
    {
        $strategy = null;
        $request = $this->event->getRequest();
        if ($request->isXmlHttpRequest()) {
            $listener->setType('ajax');
        }
        $headers = $request->getHeaders();
        if (!$headers->has('accept')) {
            return;
        }
        $accept  = $headers->get('Accept');

        // Detect JSON/JavaScript
        if (($match = $accept->match('application/json, application/javascript')) != false) {
            $typeString = $match->getTypeString();
            if ('application/json' == $typeString || 'application/javascript' == $typeString) {
                $strategy = 'ViewJsonStrategy';
                $listener->setType('json');
            }
        // Detect Feed
        } elseif (($match = $accept->match('application/rss+xml, application/atom+xml')) != false) {
            $typeString = $match->getTypeString();
            if ('application/rss+xml' == $typeString || 'application/atom+xml' == $typeString) {
                $strategy = 'ViewFeedStrategy';
                $listener->setType('feed');
            }
        }
        // Disable error debugging for AJAX, Feed and Flash
        if ($strategy || $request->isXmlHttpRequest() || $request->isFlashRequest()) {
            Pi::service('log')->debugger(false);
        }

        if (!$strategy) {
            return;
        }

        if (!isset($this->config['strategies'])) {
            $this->config['strategies'] = array();
        }

        if (in_array($strategy, $this->config['strategies'], true)) {
            return;
        } else {
            $this->config['strategies'][] = $strategy;
        }

        return;
    }
}
