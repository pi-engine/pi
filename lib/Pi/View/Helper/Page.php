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
 *  echo $this->page('about-us', __('About us),, array('target' => '_blank', 'title' => __('About the team')));
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
        $attribs    = $this->htmlAttribs($attributes);
        try {
            $href   = $this->view->url('page-page', array('name' => $name));
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
