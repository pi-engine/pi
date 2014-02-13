<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * User group APIs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Group extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /**
     * Get group list
     *
     * @return array
     */
    public function getList()
    {
        $result = array();

        $model  = Pi::model('display_group', $this->module);
        $select = $model->select()->where(array());
        $select->order('order');
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result[$row->id] = array(
                'title'    => $row->title,
                'compound' => $row->compound,
                'order'    => $row->order,
            );
            $action = $row->compound ? 'edit.compound' : 'edit.profile';
            $result[$row->id]['link'] = Pi::engine()->application()
                ->getRouter()
                ->assemble(array(
                    'module'     => $this->getModule(),
                    'controller' => 'profile',
                    'action'     => $action,
                    'group'      => $row->id
                ), array('name' => 'user')
            );
        }

        return $result;

    }
}
