<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * User module quicklink api
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Quicklink extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /**
     * Get quicklink
     *
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getList($limit = null, $offset = null)
    {
        $result  = [];
        $model   = Pi::model('quicklink', $this->module);
        $where   = [
            'active'       => 1,
            'display <> ?' => 0,
        ];
        $columns = [
            'id',
            'name',
            'title',
            'module',
            'link',
            'icon',
        ];

        $select = $model->select()->where($where);
        $select->order('display');
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->columns($columns);
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;
    }
}
