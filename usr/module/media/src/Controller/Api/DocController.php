<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ApiController;

/**
 * User webservice controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DocController extends ApiController
{
    /**
     * {@inheritDoc}
     */
    protected $modelName = 'doc';
    
    /**
     * Get handler
     */
    protected function handler()
    {
        return Pi::api('doc', $this->getModule());
    }
    
    /**
     * Add media data
     * 
     * @return array
     */
    public function insertAction()
    {
        $query = $this->params('query');
        $data  = $this->canonizeQuery($query);
        $id = $this->handler()->add($data);
        
        if (!$id) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $id
            );
        }

        return $response;
    }
    
    /**
     * Upload a file
     * 
     * @return array
     */
    public function uploadAction()
    {
        if ($this->request->isPost()) {
            $params = (array) $this->request->getPost();
        } else {
            $params = (array) $this->params();
        }
        $id = $this->handler()->upload(
            $params,
            $this->request->getMethod()
        );
        
        if (!$id) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $id
            );
        }

        return $response;
    }

    /**
     * Download a file
     *
     * @return void
     */
    public function downloadAction()
    {
        $id = $this->params('id');
        $this->handler()->download($id);
        exit;
    }

    /**
     * Update media
     * 
     * @return array
     */
    public function updateAction()
    {
        $id    = $this->params('id');
        $query = $this->params('query');
        $data  = $this->canonizeQuery($query);
        
        $result = $this->handler()->update($id, $data);
        
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
            );
        }

        return $response;
    }
    
    /**
     * Get media attributes
     * 
     * @return array
     */
    public function getAction()
    {
        $id     = $this->params('id');
        $field  = $this->params('field');
        $fields = $this->splitString($field);

        $result = $this->handler()->get($id, $fields);
        
        if (empty($result)) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $result,
            );
        }

        return $response;
    }
    
    /**
     * Get media attributes of media resources
     * 
     * @return array
     */
    public function mgetAction()
    {
        $id         = $this->params('id');
        $field      = $this->params('field');
        $ids        = $this->splitString($id);
        $fields     = $this->splitString($field);
        
        $result = $this->handler()->mget($ids, $fields);
        
        if (empty($result)) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $result,
            );
        }

        return $response;
    }
    
    /**
     * Get media statistics data
     * 
     * @return array
     */
    public function statsAction()
    {
        $id = $this->params('id');
        
        $result = $this->handler()->getStats($id);
        
        if (empty($result)) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $result,
            );
        }

        return $response;
    }
    
    /**
     * Get statistics data of media resources
     */
    public function mstatsAction()
    {
        $id         = $this->params('id');
        $ids        = $this->splitString($id);
        
        $result = $this->handler()->getStatsList($ids);
        
        if (empty($result)) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $result,
            );
        }

        return $response;
    }
    
    /**
     * Get media list according to condition
     * 
     * @return array
     */
    public function listAction()
    {
        $limit  = $this->params('limit', 0);
        $offset = $this->params('offset', 0);
        $order  = $this->params('order');
        $query  = $this->params('query');
        $field  = $this->params('field');

        $order  = $this->splitString($order);
        $fields = $this->splitString($field);
        $query  = $this->canonizeQuery($query);
        
        $where  = $this->canonizeCondition($query);
        $result = $this->handler()->getList(
            $where,
            $limit,
            $offset,
            $order,
            $fields
        );
        
        if (empty($result)) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $result,
            );
        }

        return $response;
    }
    
    /**
     * Get media count according to condition
     * 
     * @return array
     */
    public function countAction()
    {
        $query = $this->params('query');
        $query = $this->canonizeQuery($query);
        $where = $this->canonizeCondition($query);
        
        $result = $this->handler()->getCount($where);
        
        if (empty($result)) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $result,
            );
        }

        return $response;
    }
    
    /**
     * Deletes a doc
     *
     * @return array
     */
    public function deleteAction()
    {
        $response   = array(
            'status'    => 1,
        );

        $id     = $this->params('id');
        $result = $this->handler()->delete($id);
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        }

        return $response;
    }
}