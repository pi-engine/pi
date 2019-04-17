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

use Zend\View\Helper\AbstractHtmlElement;


/**
 * Helper for rendering menu
 *
 * Usage inside a phtml template, default as vertical with data attributes:
 * - label
 * - href
 * - active
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
    protected $ulClass
        = [
            'vertical'   => 'nav nav-tabs nav-stacked',
            'horizontal' => 'nav nav-tabs',
        ];

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
     * @param array $items
     *      Navigation data, list of item data or list of assembled list
     * @param array|string $attribs
     *      UL attributes or type (vertical|horizontal)
     *
     * @internal param bool $escape To escape HTML tags
     * @return string
     */
    public function __invoke(array $items, $attribs = [])
    {
        $list    = '';
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
                    $class = $item['active'] ? ' active' : '';
                    unset($item['active']);
                }
                $attr = $this->htmlAttribs($item);
                $list .= '<li class="nav-item"><a class="nav-link ' . $class . '" ' . $attr . '>'
                    . $escaper($label) . '</a></li>' . PHP_EOL;
            }
        }

        if (is_string($attribs)) {
            $attribs = [
                'type' => $attribs,
            ];
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
        $menu    = '<ul ' . $attribs . '>' . PHP_EOL . $list . '</ul>' . PHP_EOL;

        return $menu;
    }
}
