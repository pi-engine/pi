<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Laminas\Db\Sql\Expression;

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
     * @return array|\Laminas\Mvc\Controller\Plugin\Redirect
     */
    public function indexAction()
    {
        $this->view()->setTemplate('plugin');
    }

    /**
     * Timeline manage
     */
    public function timelineAction()
    {
        $page   = (int)$this->params('p', 1);
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int)($page - 1) * $limit;

        // Get list
        $model  = $this->getModel('timeline');
        $select = $model->select()->where([]);
        $select->limit($limit);
        $select->offset($offset);

        $rowset = $model->selectWith($select);

        $timeline = [];
        foreach ($rowset as $row) {
            $timeline[] = [
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
                'active' => (int)$row['active'],
            ];
        }

        // Get count
        $count  = 0;
        $select = $model->select()->where([]);
        $select->columns(['count' => new Expression('count(*)')]);
        $rowset = $model->selectWith($select);

        if ($rowset) {
            $count = $rowset->current()->count;
        }

        // Set paginator
        $paginator = [
            'count' => (int)$count,
            'limit' => $limit,
            'page'  => $page,
        ];

        return [
            'timeline'  => $timeline,
            'paginator' => $paginator,
        ];

    }

    /**
     * Activity manage
     */
    public function activityAction()
    {
        // Get display page activity
        $displayList = [];
        $model       = $this->getModel('activity');
        $where       = ['active' => 1, 'display > 0'];
        $select      = $model->select()->where($where)->order('display');
        $rowset      = $model->selectWith($select);
        foreach ($rowset as $row) {
            $displayList[] = [
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
            ];
        }

        // Get select list
        $selectList = [];
        $where      = ['active' => 1, 'display' => 0];
        $select     = $model->select()->where($where)->order('display');
        $rowset     = $model->selectWith($select);
        foreach ($rowset as $row) {
            $selectList[] = [
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
            ];
        }

        return [
            'display_list' => $displayList,
            'select_list'  => $selectList,
        ];

    }

    /**
     * Quicklink manage
     */
    public function quicklinkAction()
    {
        // Get display page quick
        $displayList = [];
        $model       = $this->getModel('quicklink');
        $where       = ['active' => 1, 'display > 0'];
        $select      = $model->select()->where($where)->order('display');
        $rowset      = $model->selectWith($select);
        foreach ($rowset as $row) {
            $displayList[] = [
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
                'link'   => $row['link'],
            ];
        }

        // Get select list
        $selectList = [];
        $where      = ['active' => 1, 'display' => 0];
        $select     = $model->select()->where($where)->order('display');
        $rowset     = $model->selectWith($select);
        foreach ($rowset as $row) {
            $selectList[] = [
                'id'     => $row['id'],
                'title'  => $row['title'],
                'module' => $row['module'],
                'link'   => $row['link'],
            ];
        }

        return [
            'display_list' => $displayList,
            'select_list'  => $selectList,
        ];
    }

    /**
     * Remove timeline from page for ajax
     *
     */
    public function toggleTimelineDisplayAction()
    {
        $id = _post('id');

        $result = [
            'status' => 0,
        ];

        if (!$id) {
            return $result;
        }

        $model = $this->getModel('timeline');
        $row   = $model->find($id, 'id');
        if ($row) {
            if (!$row->active) {
                $row->assign(['active' => 1]);

            } else {
                $row->assign(['active' => 0]);
            }
            try {
                $row->save();
                $result['status'] = 1;
            } catch (\Exception $e) {
                return $result;
            }
        }

        Pi::registry('timeline', 'user')->clear();

        return $result;

    }

    /**
     * Dress up activity from page for ajax
     *
     */
    public function dressUpActivityAction()
    {
        $ids = _post('ids');

        $result = [
            'status' => 0,
        ];

        $ids = explode(',', $ids);
        if (empty($ids)) {
            return $result;
        }

        // Get old dress up items
        $model  = $this->getModel('activity');
        $where  = ['active' => 1, 'display > 0'];
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $oldItem[] = $row['id'];
        }

        foreach ($oldItem as $id) {
            if (!in_array($id, $ids)) {
                $model->update(['display' => 0], ['id' => $id]);
            }
        }

        // Set new items
        $display = 1;
        foreach ($ids as $id) {
            $model->update(['display' => $display], ['id' => $id]);
            $display++;
        }

        $result['status'] = 1;
        Pi::registry('activity', 'user')->clear();

        return $result;

    }

    /**
     * Dress up quick from page for ajax
     *
     */
    public function dressUpQuicklinkAction()
    {
        $ids    = _post('ids');
        $result = [
            'status' => 0,
        ];

        $ids = array_unique(explode(',', $ids));

        if (empty($ids)) {
            return $result;
        }

        // Get old dress up items
        $model  = $this->getModel('quicklink');
        $where  = ['active' => 1, 'display > 0'];
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $oldItem[] = $row['id'];
        }

        foreach ($oldItem as $id) {
            if (!in_array($id, $ids)) {
                $model->update(['display' => 0], ['id' => $id]);
            }
        }

        // Set new items
        $display = 1;
        foreach ($ids as $id) {
            $model->update(['display' => $display], ['id' => $id]);
            $display++;
        }
        $result['status'] = 1;
        Pi::registry('quicklink', 'user')->clear();

        return $result;

    }
}