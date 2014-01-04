<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        return Pi::api($this->getModule(), 'doc');
    }

    public function indexAction()
    {
        $response = array(
            'status'    => 0,
            'message'   => 'Operation failed.'
        );
        return $response;
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
            $params = $this->request->getPost();
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
        $id   = $this->params('id');
        $attr = $this->params('field');
        $attr = empty($attr) ? array() : explode(',', $attr);
        
        $result = $this->handler()->get($id, $attr);
        
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
        $attr = empty($attr) ? array() : explode(',', $attr);
        
        $result = $this->handler()->mget($ids, $attr);
        
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
     * Get statistics data of medias
     */
    public function mstatsAction()
    {
        $ids = $this->params('id');
        $ids = explode(',', $ids);
        
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
        $order  = $this->params('order', '');
        $order  = empty($order) ? '' : explode(',', $order);
        $attr   = $this->params('field', '');
        $attr   = empty($attr) ? '' : explode(',', $attr);
        $query  = $this->params('query');
        $where  = $this->canonizeQuery($query);
        
        $result = $this->handler()->getList(
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
        $where = $this->canonizeQuery($query);
        
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
