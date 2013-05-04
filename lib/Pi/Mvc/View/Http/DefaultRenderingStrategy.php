<?php
/**
 * Default rendering strategy
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
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\View\Http\DefaultRenderingStrategy as ZendDefaultRenderingStrategy;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ModelInterface as ViewModel;
use Zend\Mvc\MvcEvent;

class DefaultRenderingStrategy extends ZendDefaultRenderingStrategy
{
    /**
     * Layout template for error - template used in root ViewModel of MVC event for error pages.
     *
     * @var string
     */
    protected $layoutError = 'layout-error';

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        parent::attach($events);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'initAssemble'), 10000);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'renderAssemble'), 10000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'renderAssemble'), 10000);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'completeAssemble'), 10000);
    }

    /**
     * Get error layout template value
     *
     * @return string
     */
    public function getLayoutError()
    {
        return $this->layoutError;
    }

    /**
     * Set error layout template value
     *
     * @param  string $layoutError
     * @return DefaultRenderingStrategy
     */
    public function setLayoutError($layoutError)
    {
        $this->layoutError = (string) $layoutError;
        return $this;
    }

    /**
     * Get AJAX layout template value
     *
     * @return string
     */
    public function getAjaxLayoutTemplate()
    {
        return 'layout-content';
    }

    /**
     * Render the view
     *
     * @param  MvcEvent $e
     * @return Response
     */
    public function render(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return $result;
        }

        // Martial arguments
        $request   = $e->getRequest();
        $response  = $e->getResponse();
        $viewModel = $e->getViewModel();
        if (!$viewModel instanceof ViewModel) {
            return;
        }

        // Profiling
        Pi::service('log')->start('RENDER');

        // Set up AJAX layout
        if ($request->isXmlHttpRequest()) {
            $viewModel->setTemplate($this->getAjaxLayoutTemplate());
        } else {
            // Set up error page layut
            if ($e->isError()) {
                $viewModel->setTemplate($this->getLayoutError());
            }
        }

        $view = $this->view;
        $view->setRequest($request);
        $view->setResponse($response);
        $view->render($viewModel);

        // Profiling
        Pi::service('log')->end('RENDER');

        return $response;
    }

    /**
     * Initialize assemble with config meta
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function initAssemble(MvcEvent $e)
    {
        // Skip ajax request
        $request   = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
        $viewRenderer->assemble()->initStrategy();
        return;
    }

    /**
     * Canonize head title by appending site name and/or slogan
     *
     * @param MvcEvent $e
     * @return void
     */
    public function renderAssemble(MvcEvent $e)
    {
        // Skip ajax request
        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
        $viewRenderer->assemble()->renderStrategy();
        return;
    }


    /**
     * Assemble meta contents
     *
     * @param MvcEvent $e
     * @return void
     */
    public function completeAssemble(MvcEvent $e)
    {
        // Set response headers for language and charset
        $response = $e->getResponse();
        $response->getHeaders()->addHeaders(array(
            'content-type'      => sprintf('text/html; charset=%s', Pi::config('charset')),
            'content-language'  => Pi::config('locale'),
        ));
        
        // Skip ajax request
        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
        $content = $response->getContent();
        $content = $viewRenderer->assemble()->completeStrategy($content);
        $response->setContent($content);
        return;
    }
}
