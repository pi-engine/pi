<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi\Application\Api\AbstractApi;
use Pi;

/**
 * Custom resource loader for permissions
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Permission extends AbstractApi
{
    protected $module = 'article';

    /**
     * Get categories as resources
     * 
     * @return array 
     */
    public function getResources()
    {
        $resources = array();
        $module = $this->module;
        $model  = Pi::model('category', $module);
        $rowset = $model->select(array());
        foreach ($rowset as $row) {
            $name = 'category-' . $row->name;
            $title = ucwords('category ' . $row->title);
            $resources[$name] = $title;
        }

        return $resources;
    }
}
