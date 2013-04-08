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

class PhpRendererStrategy extends ZendPhpRendererStrategy
{
    /**
     * Populate the response object from the View
     *
     * Populates the content of the response object from the view rendering
     * results.
     *
     * @param ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        parent::injectResponse($e);
        $response = $e->getResponse();
        $content = $response->getContent();
        $content = $this->assembleMeta($content);
        $response->setContent($content);
    }

    public function assembleMeta($content)
    {
        /**#@+
         * Generates and inserts head meta, stylesheets and scripts
         */
        $pos = stripos($content, '</head>');
        if (false === $pos) {
            return $content;
        }
        $preHead = substr($content, 0, $pos);
        $postHead = substr($content, $pos);
        $head = $this->renderer->headMeta() . PHP_EOL
            . $this->renderer->headLink() . PHP_EOL
            . $this->renderer->headStyle() . PHP_EOL
            . $this->renderer->headScript();
        $content = $preHead . PHP_EOL
            . $head . PHP_EOL . PHP_EOL
            . $postHead;
        /**#@-*/

        /**@+
         * Generates and inserts foot scripts
         */
        $foot = $this->renderer->footScript()->toString();
        if ($foot && $pos = strripos($content, '</body>')) {
            $preFoot = substr($content, 0, $pos);
            $postFoot = substr($content, $pos);
            $content = $preFoot . PHP_EOL
                . $foot . PHP_EOL . PHP_EOL
                . $postFoot;
        }
        /**#@-*/

        return $content;
    }
}
