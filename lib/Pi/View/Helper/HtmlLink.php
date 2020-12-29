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

use Laminas\View\Helper\AbstractHtmlElement;

/**
 * Helper for building link
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HtmlLink extends AbstractHtmlElement
{
    /**
     * Output link
     *
     * @param string $href    URL
     * @param string $label   Label to display
     * @param array  $attribs Attributes
     *
     * @return string
     */
    public function __invoke($href, $label = '', array $attribs = [])
    {
        $escapeHtml = $this->getView()->plugin('escapehtml');
        $label      = $escapeHtml($label ?: $href);
        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        return '<a href="' . $href . '"' . $attribs . '>' . $label . '</a>';// . PHP_EOL;
    }
}
