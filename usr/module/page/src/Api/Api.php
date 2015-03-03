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

    /**
     * Add a new page and register to system page settings if name is available
     *
     * @param array $page
     *
     * @return int  Page id
     */
    public function add($page)
    {
        // Set time_created
        if (!isset($page['time_created'])) {
            $page['time_created'] = time();
        }
        // Save
        $row = Pi::model('page', $this->getModule())->createRow($page);
        $row->save();
        $id = (int) $row->id;
        if (!$id) {
            return $id;
        }
        // Set system page
        if (!empty($row->name)) {
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
        }
        // Flush page registry
        Pi::registry('page', $this->getModule())->flush();
        return $id;
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

    /**
     * Get page url from its ID
     *
     * @param int $id
     *
     * @return string
     */
    public function url($id)
    {
        $params = array(
            'module'    => $this->module,
            'id'        => $id,
        );
        $pageList = Pi::registry('page', $this->module)->read();
        if (isset($pageList[$id])) {
            $params = array_merge($pageList[$id], $params);
        }
        $url = Pi::service('url')->assemble('page', $params);

        return $url;
    }
}
