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
