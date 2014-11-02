<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Page\Api;

use Pi\Application\Api\AbstractApi;

/**
 * Usage
 *
 * ```
 * Pi::api('text', 'page')->keywords($keywords);
 * Pi::api('text', 'page')->description($description);
 * Pi::api('text', 'page')->title($title);
 * Pi::api('text', 'page')->slug($slug);
 * Pi::api('text', 'page')->name($name);
 * ```
 *
 * @deprecated
 */
class Text extends AbstractApi
{
    /**
     * Invoke as a functor
     *
     * Make meta keywords from phrase
     *
     * @param string $keywords
     * @param int $number
     *
     * @return string
     *
     * @deprecated Handled by `Pi\Mvc\Controller\Plugin\View::headKeywords()`
     */
    public function keywords($keywords, $number = 6)
    {
        return $keywords;

        $keywords = _strip($keywords);
        $keywords = strtolower(trim($keywords));
        $keywords = array_unique(array_filter(explode(' ', $keywords)));
        $keywords = array_slice($keywords, 0, $number);
        $keywords = implode(',', $keywords);

        return $keywords;
    }

    /**
     * Invoke as a functor
     *
     * Make meta description from phrase
     *
     * @param  string $description
     * @return string
     *
     * @deprecated Handled by `Pi\Mvc\Controller\Plugin\View::headDescription()`
     */
    public function description($description) 
    {
        return $description;

        $description = _strip($description);
        $description = strtolower(trim($description));
        $description = preg_replace('/[\s]+/', ' ', $description);

        return $description;
    }   

    /**
     * Invoke as a functor
     *
     * Make meta title from phrase
     *
     * @param  string $title
     * @return string
     *
     * @deprecated Handled by `Pi\Mvc\Controller\Plugin\View::headTitle()`
     */
    public function title($title) 
    {
        return $title;

        $title = _strip($title);
        $title = strtolower(trim($title));
        $title = preg_replace('/[\s]+/', ' ', $title);

        return $title;
    }

    /**
     * Returns the slug
     *
     * @param string $slug
     *
     * @return string
     *
     * @deprecated
     */
    public function slug($slug)
    {
        return $slug;

        $slug = _strip($slug);
        $slug = strtolower(trim($slug));
        $slug = array_filter(explode(' ', $slug));
        $slug = implode('-', $slug);

        return $slug;
    }

    /**
     * Returns the name
     *
     * @param string $name
     *
     * @return string
     *
     * @deprecated
     */
    public function name($name)
    {
        return $name;

        $name = _strip($name);
        $name = strtolower(trim($name));
        $name = array_filter(explode(' ', $name));
        $name = implode('-', $name);

        return $name;
    }              
}