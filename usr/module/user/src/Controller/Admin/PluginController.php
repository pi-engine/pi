<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Db\Sql\Expression;
use Pi\Paginator\Paginator;

/**
 * Plugin manage cases controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class PluginController extends ActionController
{
    /**
     * Default action
     *
     * @return array|void
     */
    public function indexAction()
    {
        return $this->redirect('', array('controller' => 'plugin', 'action' => 'timeline'));
    }

    /**
     * Timeline manage
     */
    public function timelineAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        // Get list
        $model  = $this->getModel('timeline');
        $select = $model->select()->where(array());
        $select->limit($limit);
        $select->offset($offset);

        $rowset = $model->selectWith($select);

        $timeline = array();
        foreach ($rowset as $row) {
            $timeline[] = array(
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
                'active' => $row['active'],
            );
        }

        // Get count
        $count = 0;
        $select = $model->select()->where(array());
        $select->columns(array('count' => new Expression('count(*)')));
        $rowset = $model->selectWith($select);

        if ($rowset) {
            $count = $rowset->current()->count;
        }

        $paginator = Paginator::factory(intval($count), array(
            'limit' => $limit,
            'page'  => $page,
            'url_options'   => array(
                'params'    => array(

                ),
            ),
        ));

        $this->view()->assign(array(
            'timeline'  => $timeline,
            'paginator' => $paginator,
            'count'     => $count,
        ));
    }

    /**
     * Activity manage
     */
    public function activityAction()
    {
        // Get display page activity
        $displayList = array();
        $model = $this->getModel('activity');
        $where = array('active' => 1, 'display > 0');
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $displayList[] = array(
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
            );
        }

        // Get select list
        $selectList = array();
        $where  = array('active' => 1, 'display' => 0);
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $selectList[] = array(
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
            );
        }

        d($displayList);
        d($selectList);
    }

    /**
     * Quicklink manage
     */
    public function quicklinkAction()
    {
        // Get display page quick
        $displayList = array();
        $model = $this->getModel('quicklink');
        $where = array('active' => 1, 'display > 0');
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $displayList[] = array(
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
                'link'   => $row['link']
            );
        }

        // Get select list
        $selectList = array();
        $where  = array('active' => 1, 'display' => 0);
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $selectList[] = array(
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
                'link'   => $row['link']
            );
        }

        d($displayList);
        d($selectList);
    }

    /**
     * Undisplay timeline for ajax
     *
     */
    public function deleteTimelineAction()
    {
        $id = _post('id');

        $result = array(
            'status' => 0,
        );

        if (!$id) {
            return $result;
        }

        $model = $this->getModel('timeline');
        $row = $model->find($id, 'id');
        if ($row) {
            if (!$row->display) {
                $result['status'] = 1;
            } else {
                $row->assign(array('display' => 0));

                try {
                    $row->save();
                    $result['status'] = 1;
                } catch (\Exception $e) {
                    return $result;
                }
            }
        }

        return $result;

    }
}