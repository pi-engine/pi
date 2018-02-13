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
use Zend\View\Helper\HeadLink as ZendHeadLink;
use Zend\View\Helper\Placeholder;

/**
 * Helper for setting and retrieving link element for HTML head
 *
 * @see \Zend\View\Helper\HeadLink for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadLink extends ZendHeadLink
{
    /** Layout context names */
    const CONTEXT_LAYOUT = 'layout';

    /** @var array Placeholder for assets loaded by child templates */
    protected $assets = array();

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
            if (!empty($value->type) && 'text/css' == $value->type) {
                $this->assets[] = array($value, 'append');
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
            if (!empty($value->type) && 'text/css' == $value->type) {
                $this->assets[] = array($value, 'prepend');
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
            $item->conditional = null;
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

        $items = array();
        $itemsDefer = array();

        $this->getContainer()->ksort();

        // Load general config
        $configGeneral = Pi::config('', 'system', 'general');

        if (Pi::engine()->section() == 'front' && $configGeneral['compile_css']) {
            $assetsByHash = array();
            $baseUrl = Pi::url();
            $basePath = Pi::host()->path(null);

            foreach ($this->getContainer()->getArrayCopy() as $key => $item) {
                if(!empty($item->rel) && !empty($item->type) && !empty($item->href) && $item->rel == 'stylesheet' && $item->type == 'text/css' && preg_match('#' . $baseUrl . '#', $item->href)){
                    $parts = parse_url($item->href);

                    $hash = md5($parts['path'] . $parts['query']);

                    $content = file_get_contents($basePath . $parts['path']);

                    $deferHash = !empty($item->defer) && $item->defer == 'defer' ? 'defer' : 'nodefer';

                    $dirName =  dirname($parts['path']);

                    if(preg_match('#url\(#', $content)){
                        $content = str_replace('url(..', 'url(' . $dirName . '/..', $content);
                        $content = str_replace('url(\'..', 'url(\'' . $dirName . '/..', $content);
                    }

                    $assetsByHash[$deferHash][$hash] = $content;
                    $this->getContainer()->offsetUnset($key);
                }
            }

            if($assetsByHash){
                foreach($assetsByHash as $defer => $assetsByHashDefer){
                    $finalHash = md5(implode('', array_keys($assetsByHashDefer)));
                    $compiledCssDirPath = Pi::host()->path('asset/compiled/css');
                    $compiledCssDirUrl = Pi::url('asset/compiled/css');
                    $compiledCssFilePath = $compiledCssDirPath . DIRECTORY_SEPARATOR . $finalHash . '.css';
                    $compiledCssFileUrl = $compiledCssDirUrl . DIRECTORY_SEPARATOR . $finalHash . '.css';

                    if(!is_dir($compiledCssDirPath)){
                        mkdir($compiledCssDirPath, 0777, true);
                    }

                    if(!file_exists($compiledCssFilePath)){
                        file_put_contents($compiledCssFilePath, implode("\n\n\n", $assetsByHashDefer));
                    }

                    $cssObject = new stdClass();

                    $cssObject->href = $compiledCssFileUrl;
                    $cssObject->rel = 'stylesheet';
                    $cssObject->media = 'screen';

                    if($defer == 'defer'){
                        $cssObject->defer = 'defer';
                    }

                    $this->getContainer()->append($cssObject);
                }
            }
        }

        foreach ($this as $item) {
            if(isset($item->defer)){
                $itemsDefer[] = $this->itemToString($item);
            } else {
                $items[] = $this->itemToString($item);
            }
        }

        $deferString = $indent . implode($this->escape($this->getSeparator()) . $indent, $itemsDefer);

        /* @var \Pi\Application\Service\View $view */
        $view = Pi::service('view');

        $deferCssHtml = <<<HTML

<noscript id="deferred-css">
    $deferString
</noscript>
<script>
    var loadDeferredCss = function() {
        var addStylesNode = document.getElementById("deferred-css");
        var replacement = document.createElement("div");
        replacement.innerHTML = addStylesNode.textContent;
        document.body.appendChild(replacement)
        addStylesNode.parentElement.removeChild(addStylesNode);
    };
    var rafCss = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
        window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
    if (rafCss) rafCss(function() { window.setTimeout(loadDeferredCss, 0); });
    else window.addEventListener('load', loadDeferredCss);
</script>
HTML;
        $view->getHelper('footScript')->addHtml($deferCssHtml);


        return $indent . implode($this->escape($this->getSeparator()) . $indent, $items);
    }
}
