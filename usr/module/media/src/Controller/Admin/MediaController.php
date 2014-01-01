<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Module\Media\Form\MediaEditForm;
use Module\Media\Form\MediaEditFilter;
use Module\Media\Model\Detail;
use Pi\Paginator\Paginator;
use Module\Media\Service;
use Zend\Db\Sql\Expression;
use Pi;

/**
 * Media controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaController extends ActionController
{
    /**
     * Getting form instance
     * 
     * @param string  $action  Action to request when submit
     * @return \Module\Media\Form\MediaEditForm 
     */
    protected function getMediaForm($action = 'edit')
    {
        $form = new MediaEditForm();
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
            'method'  => 'post',
            'class'   => 'form-horizontal',
        ));

        return $form;
    }
    
    /**
     * Edit media
     * 
     * @return ViewModel 
     */
    public function editAction()
    {
        $id   = $this->params('id', 0);
        $row  = $this->getModel('detail')->find($id);
        if (!$row) {
            $this->view()->assign('id', $row->id);
            return;
        }
        
        $form = $this->getMediaForm('edit');
        $form->setData($row->toArray());
        
        $this->view()->assign(array(
            'form'      => $form,
            'id'        => $row->id,
        ));
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new MediaEditFilter);
            $columns = array('id', 'title', 'description');
            $form->setValidationGroup($columns);
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('There are some error occur')
                );
            }
            
            $data = $form->getData();
            $id   = $data['id'];
            unset($data['id']);
            $this->getModel('detail')->update(
                $data,
                array('id' => $id)
            );

            return $this->redirect()->toRoute(
                '',
                array(
                    'controller' => 'list',
                    'action'     => 'index'
                )
            );
        }
    }
    
    /**
     * Delete medias
     * 
     * @return ViewModel
     * @throws \Exception 
     */
    public function deleteAction()
    {
        $from   = $this->params('redirect', '');
        
        $id     = $this->params('id', 0);
        $ids    = array_filter(explode(',', $id));

        if (empty($ids)) {
            throw new \Exception(_a('Invalid media ID'));
        }
        
        // Mark media as deleted
        $this->getModel('detail')->update(
            array('delete' => 1),
            array('id' => $ids)
        );
        
        // Go to list page or original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array(
                    'controller' => 'list',
                    'action'     => 'index',
                )
            );
        }
    }
    
    /**
     * Active or diactivate media
     * 
     * @return ViewModel
     * @throws \Exception 
     */
    public function activeAction()
    {
        $from   = $this->params('redirect', '');
        
        $id     = $this->params('id', 0);
        $ids    = array_filter(explode(',', $id));
        
        if (empty($ids)) {
            throw new \Exception(_a('Invalid media ID'));
        }
        
        $status = $this->params('status', 1);
        
        // Mark media as deleted
        $this->getModel('detail')->update(
            array('active' => $status),
            array('id' => $ids)
        );
        
        // Go to list page or original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array(
                    'controller' => 'list',
                    'action'     => 'index',
                )
            );
        }
    }
}
