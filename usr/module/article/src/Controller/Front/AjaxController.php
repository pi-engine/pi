<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Ajax controller
 * 
 * Feature list:
 * 
 * 1. Fuzzy search user by name
 * 2. Fuzzy search tag
 * 3. Check whether an article is exists
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AjaxController extends ActionController
{
    const AJAX_RESULT_TRUE = 1;
    const AJAX_RESULT_FALSE = 0;

    /**
     * Get user name
     * 
     * @return array
     */
    public function getFuzzyUserAction()
    {
        Pi::service('log')->mute();
        $resultset = $result = array();

        $name  = $this->params('name', '');
        $limit = $this->params('limit', 10);

        $where = array();
        if ($name) {
            $where = Pi::db()->where();
            $where->like('identity', "{$name}%");
        }
        $uids  = Pi::user()->getUids($where, $limit, 0, 'identity ASC');
        $result = Pi::user()->get($uids, array('id', 'identity'));

        foreach ($result as $val) {
            $resultset[] = array(
                'id'   => $val['id'],
                'name' => $val['identity'],
            );
        }

        return array(
            'status'    => self::AJAX_RESULT_TRUE,
            'message'   => 'ok',
            'data'      => $resultset,
        );
    }

    /**
     * Get tag
     * 
     * @return array
     */
    /*
    public function ____getFuzzyTagAction()
    {
        Pi::service('log')->mute();
        $resultset = array();

        $name  = $this->params('name', '');
        $limit = $this->params('limit', 10);
        $limit = $limit > 100 ? 100 : $limit;
        $module = $this->getModule();
        
        if ($name && $this->config('enable_tag')) {
            $resultset = Pi::service('tag')->match($name, $limit, $module);
        }

        return array(
            'status'    => self::AJAX_RESULT_TRUE,
            'message'   => 'ok',
            'data'      => $resultset,
        );
    }
    */
    
    /**
     * Get author name by AJAX
     *  
     */
    public function getFuzzyAuthorAction()
    {
        Pi::service('log')->mute();
        $resultset = $result = array();

        $name   = $this->params('name', '');
        $limit  = $this->params('limit', 10);

        $model  = $this->getModel('author');
        $select = $model->select()
                ->columns(array('id', 'name', 'photo'))
                ->order('name ASC')
                ->limit($limit);
        if ($name) {
            $name = substr($name, 0, strpos($name, '['));
            $select->where->like('name', "{$name}%");
        }

        $result = $model->selectWith($select)->toArray();

        foreach ($result as $val) {
            $resultset[] = array(
                'id'    => $val['id'],
                'name'  => $val['name'] . '[' . $val['id'] . ']',
                'photo' => $val['photo'],
            );
        }

        echo json_encode(array(
            'status'    => true,
            'message'   => __('OK'),
            'data'      => $resultset,
        ));
        exit;
    }
}
