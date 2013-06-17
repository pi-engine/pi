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
//use Pi\View\Strategy\PhpRendererStrategy;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\ViewManager as ZendViewManager;
use Zend\Mvc\View\Http\CreateViewModelListener;
use Zend\Mvc\View\Http\InjectViewModelListener;
use Zend\Mvc\View\Http\InjectTemplateListener;

/**
 * {@inheritdoc}
 */
class ViewManager extends ZendViewManager
{
    //protected $deniedStrategy;

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
    }

    /**
     * {@inheritdoc}
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
}
