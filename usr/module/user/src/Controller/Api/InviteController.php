<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ApiController;

/**
 * Invitation webservice controller
 *
 * Methods:
 * 
 * - getLink: <appkey>, <uid>, [<mode>]
 * - get: <query>: [<uid>, <appkey>, <inviter>, <mode>, <active>], [<limit>], 
 * [<offset>], [<order>], [<field>]
 * - send: <mode>, [<content>]
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class InviteController extends ApiController
{
    /**
     * Get invitation link by passed parameters
     * required params: uid, appkey
     * optional params: mode, default direct-link
     * 
     * @return ViewModel
     */
    public function getLinkAction()
    {
        $uid    = $this->params('uid', 0);
        $appkey = $this->params('appkey', '');
        if (empty($uid) || empty($appkey)) {
            return array(
                'status' => false,
                'data'   => '',
            );
        }
        
        // Get invite generator instance
        $mode  = $this->params('mode', 'direct-link');
        $name  = ucwords(preg_replace('/[-_]/', ' ', $mode));
        $name  = str_replace(' ', '', $name);
        $class = sprintf('Custom\User\Invite\%s', $name);
        if (!class_exists($class)) {
            $class = sprintf('Module\User\Invite\%s', $name);
            if (!class_exists($class)) {
                $class = 'Module\User\Invite\DirectLink';
            }
        }

        $handle = new $class;
        $url    = $handle->generate($appkey, $uid);
        
        return array(
            'status' => true,
            'data'   => $url,
        );
    }
    
    /**
     * Get inviter info
     * 
     * @return ViewModel
     */
    public function getAction()
    {
        $limit  = $this->params('limit', 10);
        $offset = $this->params('offset', 0);
        $order  = $this->params('order');
        $field  = $this->params('field');
        $query  = $this->params('query', '');
        $query  = $this->canonizeQuery($query);
        array_walk($query, function (&$value, $key) {
            $value = explode('&', $value);
        });
        
        $order  = $this->splitString($order);
        $fields = $this->splitString($field);
        if (is_array($fields) && 1 == count($fields)) {
            $fields = array_shift($fields);
        }
        
        $columns = (array) $fields;
        if (!in_array('uid', $fields)) {
            $columns[] = 'uid';
        }
        
        $model  = $this->getModel('invite');
        $select = $model->select()
            ->where($query)
            ->limit($limit)
            ->offset($offset)
            ->order($order)
            ->columns($columns);
        $rowset = $model->selectWith($select);
        
        $result = array();
        foreach ($rowset as $row) {
            if (is_scalar($fields)) {
                $result[$row->uid] = $row->$fields;
            } else {
                $result[$row->uid] = $row->toArray();
            }
        }
        if (isset($query['uid']) && is_scalar($query['uid'])) {
            $result = $result[$query['uid']];
        }
        
        return array(
            'status' => true,
            'data'   => $result,
        );
    }
    
    public function sendAction()
    {
        
    }
}