<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;
/**
 * Helper for rendering tags
 *
 * Usage
 *
 * ```
 *  $this->tag(
 *      array('module' => <module>, 'item' => <item>, 'type' => <type>),
 *      array(), // attributes
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tag extends AbstractHtmlElement
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(array $data, $attribs = array())
    {
        if (!Pi::service('tag')->active()) {
            return '';
        }
        $module = $data['module'];
        $item = $data['item'];
        $type = isset($data['type']) ? $data['type'] : '';
        $tags = Pi::service('tag')->get($module, $item, $type, true);

        $html = '<div';
        if ($attribs) {
            $html .= $this->htmlAttribs($attribs);
        }
        $html .= '><span class="tag-label">' . __('Tags: ') . '</span>';
        foreach ($tags as $tag) {
            $html .= '<span class="tag-term">' . $tag . '</span>';
        }
        $html .= '</div>';

        return $html;
    }
}
