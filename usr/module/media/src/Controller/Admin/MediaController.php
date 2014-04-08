<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Module\Media\Form\MediaEditForm;
use Module\Media\Form\MediaEditFilter;

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
        $form->setAttribute('action', $this->url('', array('action' => $action)));

        return $form;
    }
    
    /**
     * Render form
     * 
     * @param Zend\Form\Form $form     Form instance
     * @param string         $message  Message assign to template
     * @param bool           $error    Whether is error message
     */
    public function renderForm($form, $message = null, $error = true)
    {
        $params = compact('form', 'message', 'error');
        $this->view()->assign($params);
    }
    
    /**
     * Save media data
     * 
     * @param array $data
     * @return int
     */
    protected function saveMedia($data)
    {
        $id   = $data['id'];
        unset($data['id']);
        
        $modelDoc = $this->getModel('doc');
        $rowMedia = $modelDoc->find($id);
        if ($rowMedia) {
            $rowMedia->assign($data);
            $rowMedia->save();
        } else {
            $rowMedia = $modelDoc->createRow($data);
            $rowMedia->save();
        }
        
        return $rowMedia->id;
    }
    
    /**
     * Edit media
     * 
     * @return ViewModel 
     */
    public function editAction()
    {
        $id   = $this->params('id', 0);
        $row  = $this->getModel('doc')->find($id);
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
                return $this->renderForm(
                    $form,
                    _a('There are some error occur')
                );
            }
            
            $data = $form->getData();
            $id   = $this->saveMedia($data);
            if (empty($id)) {
                return $this->renderForm(
                    $form,
                    _a('Cannot save media data')
                );
            }

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
     * Delete media resources
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
        $this->getModel('doc')->update(
            array('time_deleted' => time()),
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
        $status = $status ? 1 : 0;
        
        // Mark media as deleted
        $this->getModel('doc')->update(
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
