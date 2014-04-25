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
 * Helper for building page link
 *
 * Usage
 * ```
 *  // Display original `about-us` link
 *  echo $this->page('about-us', __('About us));
 *
 *  // Display `about-us` link with specified attributes
 *  echo $this->page('about-us', __('About us), array('target' => '_blank'));
 *
 *  // Display `about-us` link with customized hover title
 *  echo $this->page('about-us', __('About us), array('target' => '_blank', 'title' => __('About the team')));
 *
 *  // Display `about-us` link with customized hover title and display blocks assigned to `corporate` type pages
 *  echo $this->page('about-us', __('About us), array('target' => '_blank', 'title' => __('About the team'), 'type' => 'corporate'));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Page extends AbstractHtmlElement
{
    /**
     * Output page link
     *
     * @param string $name Page name or slug
     * @param string $title
     * @param array $attributes Link attributes
     *
     * @return string
     */
    public function __invoke($name, $title, $attributes = array())
    {
        $pattern    = '<a href="%s"%s>%s</a>';
        if (!isset($attributes['title'])) {
            $attributes['title'] = $title;
        }
        $type = '';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
            unset($attributes['type']);
        }
        $attribs    = $this->htmlAttribs($attributes);
        try {
            $params = array('name' => $name);
            if ($type) {
                $params['type'] = $type;
            }
            $href   = $this->view->url('page-page', $params);
        } catch (\Exception $e) {
            $href   = '#';
        }

        $html = sprintf(
            $pattern,
            $href,
            $attribs,
            $title
        );

        return $html;
    }
}
