<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use stdClass;
use Laminas\View\Helper\HeadLink as LaminasHeadLink;
use Laminas\View\Helper\Placeholder;

/**
 * Helper for setting and retrieving link element for HTML head
 *
 * @see    \Laminas\View\Helper\HeadLink for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadLink extends LaminasHeadLink
{
    /**
     * Allowed attributes
     *
     * @var string[]
     */
    protected $itemKeys
        = [
            'charset',
            'href',
            'hreflang',
            'id',
            'media',
            'rel',
            'rev',
            'sizes',
            'type',
            'title',
            'extras',
            'onload',
            'as',
        ];

    /** Layout context names */
    const CONTEXT_LAYOUT = 'layout';

    /** @var array Placeholder for assets loaded by child templates */
    protected $assets = [];

    /**
     * {@inheritDoc}
     * @return self
     */
    public function __invoke(
        array $attributes = null,
        $placement = Placeholder\Container\AbstractContainer::APPEND
    ) {
        parent::__invoke($attributes, strtoupper($placement));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function append($value)
    {
        $context = $this->view->context();
        if ($context && $context != static::CONTEXT_LAYOUT) {
            if (!empty($value->type) && 'text/css' == $value->type && !in_array([$value, 'append'], $this->assets)) {
                $this->assets[] = [$value, 'append'];
                return;
            }
        }

        return parent::append($value);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend($value)
    {
        $context = $this->view->context();
        if ($context && $context != static::CONTEXT_LAYOUT) {
            if (!empty($value->type) && 'text/css' == $value->type && !in_array([$value, 'append'], $this->assets)) {
                $this->assets[] = [$value, 'prepend'];
                return;
            }
        }

        return parent::prepend($value);
    }

    /**
     * {@inheritDoc}
     *  Canonize attribute 'conditional' with 'conditionalStylesheet'
     */
    public function itemToString(stdClass $item)
    {
        if (isset($item->conditional)) {
            $item->conditionalStylesheet = $item->conditional;
            $item->conditional           = null;
        }

        return parent::itemToString($item);
    }

    /**
     * {@inheritDoc}
     * Load module assets
     */
    public function toString($indent = null)
    {
        if ($this->assets) {
            foreach ($this->assets as $item) {
                switch ($item[1]) {
                    case 'prepend':
                        parent::prepend($item[0]);
                        break;
                    default:
                        parent::append($item[0]);
                        break;
                }
            }
        }

        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        $items              = [];
        $itemsDefer         = [];
        $itemsDeferNoScript = [];

        $this->getContainer()->ksort();

        // Load general config
        $configGeneral = Pi::config('', 'system', 'general');

        if (Pi::engine()->section() == 'front' && $configGeneral['compile_css']) {
            $assetsByHash = [];
            $baseUrl      = Pi::url();
            $basePath     = Pi::host()->path(null);

            foreach ($this->getContainer()->getArrayCopy() as $key => $item) {
                if (!empty($item->rel) && !empty($item->type) && !empty($item->href) && $item->rel == 'stylesheet' && $item->type == 'text/css'
                    && preg_match(
                        '#' . $baseUrl . '#',
                        $item->href
                    )
                ) {
                    $parts = parse_url($item->href);

                    $query = !empty($parts['query']) ? $parts['query'] : '';
                    $hash  = md5($parts['path'] . $query);

                    $content = file_get_contents($basePath . str_replace($baseUrl, '', strtok($item->href, '?')));

                    $deferHash = !empty($item->defer) && $item->defer == 'defer' ? 'defer' : 'nodefer';

                    $dirName = dirname($parts['path']);

                    if (preg_match('#url\(#', $content)) {
                        /**
                         * Keep from external url to be processed
                         */
                        $content = str_replace('url(http', 'url_tmp(http', $content);

                        /**
                         * Replace local files url directives
                         * Escape data-image
                         */
                        $content = str_replace('url(data:image', 'url_tmp(data:image', $content);
                        $content = str_replace('url("data:image', 'url_tmp("data:image', $content);
                        $content = str_replace('url(\'.', 'url_tmp(\'' . $dirName . '/.', $content);
                        $content = str_replace('url(.', 'url_tmp(' . $dirName . '/.', $content);
                        $content = str_replace('url("', 'url_tmp("' . $dirName . '/', $content);
                        $content = str_replace('url(', 'url_tmp(' . $dirName . '/', $content);

                        /**
                         * Roll back processed lines
                         */
                        $content = str_replace('url_tmp', 'url', $content);
                    }

                    $assetsByHash[$deferHash][$hash] = $content;
                    $this->getContainer()->offsetUnset($key);
                }
            }

            if ($assetsByHash) {
                foreach ($assetsByHash as $defer => $assetsByHashDefer) {
                    $finalHash           = md5(implode('', array_keys($assetsByHashDefer)));
                    $compiledCssDirPath  = Pi::host()->path('asset/compiled/css');
                    $compiledCssDirUrl   = Pi::url('asset/compiled/css');
                    $compiledCssFilePath = $compiledCssDirPath . DIRECTORY_SEPARATOR . $finalHash . '.css';
                    $compiledCssFileUrl  = $compiledCssDirUrl . DIRECTORY_SEPARATOR . $finalHash . '.css';

                    if (!is_dir($compiledCssDirPath)) {
                        mkdir($compiledCssDirPath, 0777, true);
                    }

                    if (!file_exists($compiledCssFilePath)) {
                        file_put_contents($compiledCssFilePath, implode("\n\n\n", $assetsByHashDefer));
                    }

                    $cssObject = new stdClass();

                    $cssObject->href  = $compiledCssFileUrl;
                    $cssObject->rel   = 'stylesheet';
                    $cssObject->media = 'screen';

                    if ($defer == 'defer') {
                        $cssObject->defer = 'defer';
                    }

                    $this->getContainer()->append($cssObject);
                }
            }
        }

        $isUserSection      = new IsUserSection();
        $module             = Pi::service('module')->current();
        $isUserSectionValue = $isUserSection->__invoke($module);

        foreach ($this as $item) {
            if (!$isUserSectionValue && isset($item->defer)) {
                $itemsDeferNoScript[] = $this->itemToString($item);

                $item->rel    = 'preload';
                $item->as     = 'style';
                $item->onload = "this.onload=null;this.rel='stylesheet'";

                $this->setAutoEscape(false);
                $itemsDefer[] = $this->itemToString($item);
            } else {
                $this->setAutoEscape(true);
                $items[] = $this->itemToString($item);
            }
        }

        if ($itemsDefer) {
            $deferString         = $indent . implode($this->escape($this->getSeparator()) . $indent, $itemsDefer);
            $deferNoScriptString = $indent . implode($this->escape($this->getSeparator()) . $indent, $itemsDeferNoScript);

            /* @var \Pi\Application\Service\View $view */
            $view = Pi::service('view');

            /*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
            /* This file is meant as a standalone workflow for
            - testing support for link[rel=preload]
            - enabling async CSS loading in browsers that do not support rel=preload
            - applying rel preload css once loaded, whether supported or not.
            */

            $deferCssHtml = <<<HTML

$deferString

<noscript id="deferred-css">
    $deferNoScriptString
</noscript>
<script>
    !function(t){"use strict";t.loadCSS||(t.loadCSS=function(){});var e=loadCSS.relpreload={};if(e.support=function(){var e;try{e=t.document.createElement("link").relList.supports("preload")}catch(t){e=!1}return function(){return e}}(),e.bindMediaToggle=function(t){var e=t.media||"all";function a(){t.media=e}t.addEventListener?t.addEventListener("load",a):t.attachEvent&&t.attachEvent("onload",a),setTimeout(function(){t.rel="stylesheet",t.media="only x"}),setTimeout(a,3e3)},e.poly=function(){if(!e.support())for(var a=t.document.getElementsByTagName("link"),n=0;n<a.length;n++){var o=a[n];"preload"!==o.rel||"style"!==o.getAttribute("as")||o.getAttribute("data-loadcss")||(o.setAttribute("data-loadcss",!0),e.bindMediaToggle(o))}},!e.support()){e.poly();var a=t.setInterval(e.poly,500);t.addEventListener?t.addEventListener("load",function(){e.poly(),t.clearInterval(a)}):t.attachEvent&&t.attachEvent("onload",function(){e.poly(),t.clearInterval(a)})}"undefined"!=typeof exports?exports.loadCSS=loadCSS:t.loadCSS=loadCSS}("undefined"!=typeof global?global:this);;
</script>
HTML;
            if (!isset($_GET['test_flash']) || $_GET['test_flash'] != 1) {
                $view->getHelper('footScript')->addHtml($deferCssHtml);
            }
        }

        return $indent . implode($this->escape($this->getSeparator()) . $indent, $items);
    }
}
