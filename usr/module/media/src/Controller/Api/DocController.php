<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Controller\Api;

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
     * Add media data
     * 
     * @return array
     */
    public function addAction()
    {
        $query = (array) $this->params('query');
        $data  = $this->canonizeQuery($query);
        $id = Pi::api($this->module, 'doc')->add($data);
        
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
        $params = (array) $this->params();
        $id = Pi::api($this->module, 'doc')->upload(
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
     * Update media
     * 
     * @return array
     */
    public function updateAction()
    {
        $id    = $this->params('id');
        $query = $this->params('query');
        $data  = $this->canonizeQuery($query);
        
        $result = Pi::api($this->module, 'doc')->update($id, $data);
        
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
        $id   = $this->params('id');
        $attr = $this->params('field');
        $attr = explode(',', $attr);
        
        $result = Pi::api($this->module, 'doc')->get($id, $attr);
        
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
     * Get media attributes of medias
     * 
     * @return array
     */
    public function mgetAction()
    {
        $ids  = $this->params('id');
        $ids  = explode(',', $ids);
        $attr = $this->params('field');
        $attr = explode(',', $attr);
        
        $result = Pi::api($this->module, 'doc')->mget($ids, $attr);
        
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
        
        $result = Pi::api($this->module, 'doc')->getStats($id);
        
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
     * Get statistics data of medias
     */
    public function mstatsAction()
    {
        $ids = $this->params('id');
        $ids = explode(',', $ids);
        
        $result = Pi::api($this->module, 'doc')->getStatsList($ids);
        
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
        $order  = $this->params('order', '');
        $order  = explode(',', $order);
        $attr   = $this->params('field');
        $attr   = explode(',', $attr);
        $query  = $this->params('query');
        $query  = $this->canonizeQuery($query);
        $where  = $this->canonizeCondition($query);
        
        $result = Pi::api($this->module, 'doc')->getList(
            $where,
            $limit,
            $offset,
            $order,
            $attr
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
        
        $result = Pi::api($this->module, 'doc')->getCount($where);
        
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
        $result = Pi::api($this->module, 'doc')->delete($id);
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        }

        return $response;
    }
}
