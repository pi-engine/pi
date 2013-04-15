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

use Zend\View\Strategy\PhpRendererStrategy as ZendPhpRendererStrategy;
use Zend\View\ViewEvent;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;

class PhpRendererStrategy extends ZendPhpRendererStrategy
{
    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        parent::attach($events, $priority);
        //$this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'assembleMeta'), $priority);
    }

    /**
     * Populate the response object from the View
     *
     * Populates the content of the response object from the view rendering
     * results.
     *
     * @param ViewEvent $e
     * @return void
     */
    public function assembleMeta(ViewEvent $e)
    {
        $response = $e->getResponse();
        $content = $response->getContent();

        /**#@+
         * Generates and inserts head meta, stylesheets and scripts
         */
        $pos = stripos($content, '</head>');
        if (false === $pos) {
            return;
        }
        $preHead = substr($content, 0, $pos);
        $postHead = substr($content, $pos);

        $indent = 4;

        $headTitle = '';
        if ($this->renderer->headTitle()->count()) {
            $headTitle = $this->renderer->headTitle()->toString($indent);
            $headTitle .= $headTitle;
        }

        $headMeta = $this->renderer->headMeta()->toString($indent);
        $headMeta .= $headMeta ? PHP_EOL : '';

        $headLink = $this->renderer->headLink()->toString($indent);
        $headLink .= $headLink ? PHP_EOL : '';

        $headStyle = $this->renderer->headStyle()->toString($indent);
        $headStyle .= $headStyle ? PHP_EOL : '';

        $headScript = $this->renderer->headScript()->toString($indent);
        $headScript .= $headScript ? PHP_EOL : '';

        $head = $headTitle . $headMeta . $headLink . $headStyle . $headScript;
        $content = $preHead . ($head ? PHP_EOL . $head . PHP_EOL : '') . $postHead;
        /**#@-*/

        /**@+
         * Generates and inserts foot scripts
         */
        $foot = $this->renderer->footScript()->toString($indent);
        if ($foot && $pos = strripos($content, '</body>')) {
            $preFoot = substr($content, 0, $pos);
            $postFoot = substr($content, $pos);
            $content = $preFoot . PHP_EOL . $foot . PHP_EOL . PHP_EOL . $postFoot;
        }
        /**#@-*/

        $response->setContent($content);
    }
}
