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
 * Helper for rendering menu
 *
 * Usage inside a phtml template, default as vertical
 *
 * ```
 *  $this->navMenu($data, 'horizontal');
 *  $this->navMenu($data);
 * ```
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavMenu extends AbstractHtmlElement
{
    /**
     * UL class definitions
     * @var array
     */
    protected $ulClass = array(
        'vertical'      => 'nav nav-tabs nav-stacked',
        'horizontal'    => 'nav nav-tabs',
    );

    /**
     * Render a navigation menu
     *
     * With items:
     *
     * - List of item data
     *
     *  ```
     *      array(
     *          array(
     *              'href'      => $link,
     *              'label'     => $label,
     *              'active'    => 1,
     *          ),
     *          array(
     *              'href'      => $link,
     *              'label'     => $label,
     *          ),
     *      );
     *  ```
     * - List of assembled data
     *
     *  ```
     *  array(
     *      '<li class="active"><a href="link" title="label">label</a></li>',
     *      '<li><a href="link" title="label">label</a></li>',
     * );
     *  ```
     *
     * @param array         $items
     *      Navigation data, list of item data or list of assembled list
     * @param array|string  $attribs
     *      UL attributes or type (vertical|horizontal)
     * @param bool          $escape     To escape HTML tags
     * @return string
     */
    public function __invoke(array $items, $attribs = array())
    {
        $list = '';
        $escaper = $this->view->plugin('escapeHtml');
        foreach ($items as $item) {
            if (!is_array($item)) {
                $list .= $item . PHP_EOL;
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
                $list .= '<li' . $class . '><a' . $attr . '>'
                       . $escaper($label) . '</a></li>' . PHP_EOL;
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
        $menu = '<ul ' . $attribs . '>' . PHP_EOL . $list . '</ul>' . PHP_EOL;

        return $menu;
    }
}
