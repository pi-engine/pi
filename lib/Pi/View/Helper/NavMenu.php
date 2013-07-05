<?php
/**
 * Menu helper
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
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;


/**
 * Helper for rendering menu
 *
 * Usage inside a phtml template, default as vertical:
 * <code>
 *  $this->navMenu($data, 'horizontal');
 *  $this->navMenu($data);
 * </code>
 */
class NavMenu extends AbstractHtmlElement
{
    protected $ulClass = array(
        'vertical'      => 'nav nav-tabs nav-stacked',
        'horizontal'    => 'nav nav-tabs',
    );

    /**
     * Render
     *
     * @param array         $items      navigation data, list of item data or list of assembled list
     *                          array(
     *                              array(
     *                                  'href'      => $link,
     *                                  'label'     => $label,
     *                                  'active'    => 1,
     *                              ),
     *                              array(
     *                                  'href'      => $link,
     *                                  'label'     => $label,
     *                              ),
     *                          );
     *
     *                          array(
     *                              '<li class="active"><a href="link" title="label">label</a></li>',
     *                              '<li><a href="link" title="label">label</a></li>',
     *                          );
     *
     * @param array|string  $attribs    ul attributes or type (vertical|horizontal)
     * @param boolean       $escape     escape HTML tags
     * @return  string
     */
    public function __invoke(array $items, $attribs = array())
    {
        $list = '';
        $escaper = $this->view->plugin('escapeHtml');
        foreach ($items as $item) {
            if (!is_array($item)) {
                $list .= $item . static::EOL;
            } else {
                $label = $item['label'];
                unset($item['label']);
                if (!isset($item['title'])) {
                    $item['title'] = $label;
                }
                $class = '';
                if (isset($item['active'])) {
                    $class = $item['active'] ? ' class="active"' : '';
                    unset($item['active']);
                }
                $attr = $this->htmlAttribs($item);
                $list .= '<li' . $class . '><a' . $attr . '>' . $escaper($label) . '</a></li>' . static::EOL;
            }
        }

        if (is_string($attribs)) {
            $attribs = array(
                'type'  => $attribs,
            );
        }
        if (empty($attribs['class'])) {
            $type = 'vertical';
            if (!empty($attribs['type'])) {
                $type = $attribs['type'];
                unset($attribs['type']);
            }
            if ('horizontal' != $type) {
                $type = 'vertical';
            }

            $attribs['class'] = $this->ulClass[$type];
        }

        $attribs = $this->htmlAttribs($attribs);
        $menu = '<ul ' . $attribs . '>' . static::EOL . $list . '</ul>' . static::EOL;

        return $menu;
    }
}
