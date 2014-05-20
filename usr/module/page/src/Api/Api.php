<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

class Api extends AbstractApi
{
    protected $module = 'page';
    protected $pageColumns = array(
        'name', 'title', 'slug', 'content', 'markup', 'active',
        'user', 'time_created', 'seo_title', 'seo_keywords', 'seo_description'
    );

    /**
     * Add a new page and register to system page settings if name is available
     *
     * @param array $page
     * @return boolean
     */
    public function add($page)
    {
        foreach (array_keys($page) as $key) {
            if (!in_array($key, $this->pageColumns)) {
                unset($page[$key]);
            }
        }
        // Set time_created
        if (!isset($page['time_created'])) {
            $page['time_created'] = time();
        }
        // Set name
        $page['name'] = $page['name'] ? Pi::api('text', 'page')->name($page['name']) : null;
        // Set slug
        $page['slug'] = $page['slug'] ? Pi::api('text', 'page')->slug($page['slug']) : null;
        // Set seo_title
        $page['seo_title'] = Pi::api('text', 'page')->title($page['title']);
        // Set seo_keywords
        $page['seo_keywords'] = Pi::api('text', 'page')->keywords($page['title']);
        // Set seo_description
        $page['seo_description'] = Pi::api('text', 'page')->description($page['title']);
        // Save
        $row = Pi::model('page', $this->getModule())->createRow($page);
        $row->save();
        if (!$row->id) {
            return false;
        }

        if (!$row->name) {
            return true;
        }
        $page = array(
            'section'       => 'front',
            'module'        => $this->getModule(),
            'controller'    => 'index',
            'action'        => $row->name,
            'title'         => $row->title,
            'block'         => 1,
            'custom'        => 0,
        );
        $row = Pi::model('page')->createRow($page);
        $row->save();

        Pi::registry('page')->clear($this->getModule());

        return $row->id ? true : false;
    }

    /**
     * Delete a page and remove from system settings
     *
     * @param string|int $name Name or ID
     * @return boolean
     */
    public function delete($name)
    {
        if (is_int($name)) {
            $row = Pi::model('page', $this->getModule())->find($name);
        } else {
            $row = Pi::model('page', $this->getModule())->find($name, 'name');
        }
        if (!$row) {
            return false;
        }
        $row->delete();

        if (!$row->name) {
            return true;
        }
        $where = array(
            'section'       => 'front',
            'module'        => $this->getModule(),
            'controller'    => 'index',
            'action'        => $row->name,
        );
        Pi::model('page')->delete($where);

        Pi::registry('page')->clear($this->getModule());

        return true;
    }
}
