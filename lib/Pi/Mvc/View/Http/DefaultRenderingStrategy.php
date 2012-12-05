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

            // Set up layout meta data
            $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
            $viewRenderer->meta()->assign();
        }

        $view = $this->view;
        $view->setRequest($request);
        $view->setResponse($response);
        $view->render($viewModel);

        // Profiling
        Pi::service('log')->end('RENDER');

        return $response;
    }
}
