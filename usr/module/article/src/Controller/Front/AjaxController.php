<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Module\Article\Service;
use Pi;

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
        Pi::service('log')->active(false);
        $resultset = $result = array();

        $name  = Service::getParam($this, 'name', '');
        $limit = Service::getParam($this, 'limit', 10);

        $model = Pi::model('user');
        $select = $model->select()
            ->columns(array('id', 'name' => 'identity'))
            ->order('identity ASC')
            ->limit($limit);
        if ($name) {
            $select->where->like('identity', "{$name}%");
        }

        $result = $model->selectWith($select)->toArray();

        foreach ($result as $val) {
            $resultset[] = array(
                'id'   => $val['id'],
                'name' => $val['name'],
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
    public function getFuzzyTagAction()
    {
        Pi::service('log')->active(false);
        $resultset = array();

        $name  = Service::getParam($this, 'name', '');
        $limit = Service::getParam($this, 'limit', 10);
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
    
    /**
     * Get author name by AJAX
     *  
     */
    public function getFuzzyAuthorAction()
    {
        Pi::service('log')->active(false);
        $resultset = $result = array();

        $name   = Service::getParam($this, 'name', '');
        $limit  = Service::getParam($this, 'limit', 10);

        $model  = $this->getModel('author');
        $select = $model->select()
                ->columns(array('id', 'name', 'photo'))
                ->order('name ASC')
                ->limit($limit);
        if ($name) {
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
