<?php
/**
 * PhpRendererStrategy
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
 * @package         Pi\View
 * @version         $Id$
 */

namespace Pi\View\Strategy;

use Pi;
use Zend\View\Strategy\PhpRendererStrategy as ZendPhpRendererStrategy;
use Zend\View\ViewEvent;
use Zend\EventManager\EventManagerInterface;

class PhpRendererStrategy extends ZendPhpRendererStrategy
{
    protected $initialized = false;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        parent::attach($events, $priority);

        /*
        // The initAssemble should be called before all view rendering
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'initAssemble'), 10000);

        // The renderAssemble should be called after all view rendering
        //$this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER_POST, array($this, 'renderAssemble'), 10000);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'renderAssemble'), 10000);

        // The completeAssemble should be called after all view rendering and before response is sent
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'completeAssemble'), -10000);
        */
    }

    /**
     * Initialize assemble with config meta
     *
     * @param  ViewEvent $e
     * @return void
     */
    public function initAssemble(ViewEvent $e)
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        // Skip ajax request
        $request   = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        d(__METHOD__);
        $this->renderer->assemble()->initStrategy();
        return;
    }

    /**
     * Canonize head title by appending site name and/or slogan
     *
     * @param ViewEvent $e
     * @return void
     */
    public function renderAssemble(ViewEvent $e)
    {
        // Skip ajax request
        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        $this->renderer->assemble()->renderStrategy();
        return;
    }

    /**
     * Assemble meta contents
     *
     * @param ViewEvent $e
     * @return void
     */
    public function completeAssemble(ViewEvent $e)
    {
        // Set response headers for language and charset
        $response = $e->getResponse();
        $response->getHeaders()->addHeaders(array(
            'content-type'      => sprintf('text/html; charset=%s', Pi::service('i18n')->charset),
            'content-language'  => Pi::service('i18n')->locale,
        ));

        // Skip ajax request
        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        $content = $response->getContent();
        $content = $this->renderer->assemble()->completeStrategy($content);
        $response->setContent($content);
        return;
    }
}
