<?php
/**
 * Pi theme rendering strategy
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
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface as ViewModel;
use Zend\View\Model\ConsoleModel;
use Zend\View\Model\FeedModel;
use Zend\View\Model\JsonModel;


class ThemeRenderingStrategy extends AbstractListenerAggregate
{
    protected $skip = null;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'initAssemble'), 10000);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'renderAssemble'), 10000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'renderAssemble'), 10000);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'setLayout'), 1000);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'completeAssemble'), 10000);
    }

    public function setSkip($skip)
    {
        $this->skip = $skip;
        return $this;
    }

    protected function skip(MvcEvent $e)
    {
        if (null !== $this->skip) {
            return $this->skip;
        }

        $_this = $this;
        $setSkip = function ($skip = true) use ($_this) {
            $_this->setSkip($skip);
            return $skip;
        };

        $result = $e->getResult();
        if ($result instanceof Response) {
            return $setSkip();
        }

        // Skip AJAX request
        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return $setSkip();
        }

        $viewModel = $e->getViewModel();
        if (!$viewModel instanceof ViewModel || !$viewModel->getTemplate()) {
            return $setSkip();
        }

        return $this->skip;
    }

    /**
     * {@inheritDoc}
     */
    public function setLayout(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return $result;
        }

        $viewModel = $e->getViewModel();
        if (!$viewModel instanceof ViewModel || $viewModel instanceof JsonModel || $viewModel instanceof FeedModel) {
            return;
        }

        $config     = $e->getApplication()->getServiceManager()->get('Config');
        $viewConfig = $config['view_manager'];
        $request    = $e->getRequest();
        // Set up AJAX layout
        if ($request->isXmlHttpRequest()) {
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
    public function initAssemble(MvcEvent $e)
    {
        if ($this->skip($e)) {
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
        if ($this->skip($e)) {
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
        if ($this->skip($e)) {
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
        return;
    }
}
