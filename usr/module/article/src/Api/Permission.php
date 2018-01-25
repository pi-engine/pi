<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

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
        $resources = [];
        $module    = $this->module;
        $model     = Pi::model('category', $module);
        $rowset    = $model->select([]);
        foreach ($rowset as $row) {
            $name             = 'category-' . $row->name;
            $title            = ucwords('category ' . $row->title);
            $resources[$name] = $title;
        }

        return $resources;
    }
}
