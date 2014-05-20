<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
 *  // Render
 *  $this->tag(
 *      array('module' => <module>, 'item' => <item>, 'type' => <type>),
 *      array() // attributes
 *  );
 *
 *  // Render with auto-load
 *  $this->tag(
 *      '',
 *      array() // attributes
 *  );
 *
 * // Render with template
 *  $this->tag(
 *      array('module' => <module>, 'item' => <item>, 'type' => <type>),
 *      '<template>' // template
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tag extends AbstractHtmlElement
{
    /**
     * Renders tag component
     *
     * @param array|string $data Data or URL to identify tagged item
     * @param array|string $attribs Attributes or template
     *
     * @return string
     */
    public function __invoke($data = array(), $attribs = array())
    {
        if (!Pi::service('tag')->active()) {
            return '';
        }
        if (!$data || is_string($data)) {
            if (!$data) {
                $routeMatch = Pi::service('url')->getRouteMatch();
            } else {
                $routeMatch = Pi::service('url')->match($data);
            }
            $module = $routeMatch->getParam('module');
            $item   = $routeMatch->getParam('id');
            $type   = '';
        } else {
            $module = isset($data['module'])
                ? $data['module']
                : Pi::service('module')->current();
            $item   = $data['item'];
            $type   = isset($data['type']) ? $data['type'] : '';
        }

        if (!$module || !$item) {
            return '';
        }

        $tags = Pi::service('tag')->get($module, $item, $type);
        if (empty($tags)) return '';

        array_walk($tags, function (&$tag) use ($module, $type) {
            $tag = Pi::service('tag')->render($tag, $module, $type);
        });
        
        if ($attribs && is_string($attribs)) {
            $html = $this->getView()->render($attribs, array('tags' => $tags));
        } else {
            $html = '<div class="tag-terms"';
            if ($attribs) {
                $html .= $this->htmlAttribs($attribs);
            }
            $html .= '><span class="tag-label">' . __('Tags: ') . '</span>';
            foreach ($tags as $tag) {
                $html .= $tag;
            }
            $html .= '</div>';
        }

        return $html;
    }
}
