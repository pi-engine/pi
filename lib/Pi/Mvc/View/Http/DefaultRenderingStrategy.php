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

        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'loadMeta'), 10000);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'canonizeTitle'), 10000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'canonizeTitle'), 10000);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'assembleMeta'), 10000);
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
     * Load config meta
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function loadMeta(MvcEvent $e)
    {
        // Skip ajax request
        $request   = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');

        // Load meta config
        $configMeta = Pi::service('registry')->config->read('system', 'meta');
        // Set head meta
        foreach ($configMeta as $key => $value) {
            if (!$value) {
                continue;
            }
            $viewRenderer->headMeta()->appendName($key, $value);
        }

        // Load general config
        $configGeneral = Pi::service('registry')->config->read('system');

        // Set Google Analytics scripts in case available
        if ($configGeneral['ga_account']) {
            $viewRenderer->footScript()->appendScript($viewRenderer->ga($configGeneral['ga_account']));
        }
        // Set foot scripts in case available
        if ($configGeneral['foot_script']) {
            if (false !== stripos($configGeneral['foot_script'], '<script ')) {
                $viewRenderer->footScript()->appendScript($configGeneral['foot_script'], 'raw');
            } else {
                $viewRenderer->footScript()->appendScript($configGeneral['foot_script']);
            }
        }
        unset($configGeneral['ga_account'], $configGeneral['foot_script']);

        // Set global variables to root ViewModel, e.g. theme template
        $viewRenderer->plugin('view_model')->getRoot()->setVariables($configGeneral);
    }

    /**
     * Canonize head title by appending site name and/or slogan
     *
     * @param MvcEvent $e
     * @return void
     */
    public function canonizeTitle(MvcEvent $e)
    {
        // Skip ajax request
        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        $headTitle = $e->getApplication()->getServiceManager()->get('ViewRenderer')->headTitle();
        $hasCustom = $headTitle->count();
        $headTitle->setSeparator(' - ');

        // Append module name for non-system module
        $currentModule = Pi::service('module')->current();
        if ($currentModule && 'system' != $currentModule) {
            $moduleMeta = Pi::service('registry')->module->read($currentModule);
            $headTitle->append($moduleMeta['title']);
        }
        // Append site name
        $headTitle->append(Pi::config('sitename'));

        // Append site slogan if no custom title available
        if (!$hasCustom) {
            $headTitle->append(Pi::config('slogan'));
        }
    }


    /**
     * Assemble meta contents
     *
     * @param MvcEvent $e
     * @return void
     */
    public function assembleMeta(MvcEvent $e)
    {
        $response = $e->getResponse();
        $content = $response->getContent();

        $pos = stripos($content, '</head>');
        if (false === $pos) {
            return;
        }

        // Get ViewRenderer
        $viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');

        /**#@+
         * Generates and inserts head meta, stylesheets and scripts
         */
        $preHead = substr($content, 0, $pos);
        $postHead = substr($content, $pos);

        $indent = 4;

        $headTitle = '';
        /*
        if ($viewRenderer->headTitle()->count()) {
            $headTitle = $viewRenderer->headTitle()->toString($indent) . PHP_EOL;
        }
        */

        $headMeta = $viewRenderer->headMeta()->toString($indent);
        $headMeta .= $headMeta ? PHP_EOL : '';

        $headLink = $viewRenderer->headLink()->toString($indent);
        $headLink .= $headLink ? PHP_EOL : '';

        $headStyle = $viewRenderer->headStyle()->toString($indent);
        $headStyle .= $headStyle ? PHP_EOL : '';

        $headScript = $viewRenderer->headScript()->toString($indent);
        $headScript .= $headScript ? PHP_EOL : '';

        $head = $headTitle . $headMeta . $headLink . $headStyle . $headScript;
        $content = $preHead . ($head ? PHP_EOL . $head . PHP_EOL : '') . $postHead;
        /**#@-*/

        /**@+
         * Generates and inserts foot scripts
         */
        $foot = $viewRenderer->footScript()->toString($indent);
        if ($foot && $pos = strripos($content, '</body>')) {
            $preFoot = substr($content, 0, $pos);
            $postFoot = substr($content, $pos);
            $content = $preFoot . PHP_EOL . $foot . PHP_EOL . PHP_EOL . $postFoot;
        }
        /**#@-*/

        $response->setContent($content);
    }
}
