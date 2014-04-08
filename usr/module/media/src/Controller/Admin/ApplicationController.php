<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Media\Form\AppEditForm;
use Module\Media\Form\AppEditFilter;
use Zend\Db\Sql\Expression;
use Pi\Paginator\Paginator;

/**
 * Application controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ApplicationController extends ActionController
{
    /**
     * Available columns of application
     * @var array
     */
    protected $columns = array('id', 'appkey', 'title');
    
    /**
     * Getting form instance
     * 
     * @param string  $action  Action to request when submit
     * @return \Module\Media\Form\AppEditForm 
     */
    protected function getApplicationForm($action = 'edit')
    {
        $form = new AppEditForm();
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
            'method'  => 'post',
            'class'   => 'form-horizontal',
        ));

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
     * Application list page
     * 
     * @return ViewModel
     */
    public function listAction()
    {
        $page   = $this->params('p', 1);
        $name   = $this->params('name', '');
        $limit  = $this->config('page_limit') > 0
            ? $this->config('page_limit') : 20;
        $offset = $limit * ($page - 1);

        $where = array();
        if ($name) {
            $where['title like ?'] = "%{$name}%";
        }
        $module = $this->getModule();
        $model  = $this->getModel('application');
        $select = $model->select()
            ->order('id ASC')->offset($offset)->limit($limit);
        $resultset = $model->selectWith($select)->toArray();

        // Total count
        $totalCount = $model->count($where);

        // Paginator
        $paginator = Paginator::factory($totalCount, array(
            'page'          => $page,
            'url_options'   => array(
                'page_param'    => 'p',
                'params'     => array_filter(array(
                    'module'        => $module,
                    'controller'    => 'application',
                    'action'        => 'list',
                    'name'          => $name,
                )),
            ),
        ));

        $this->view()->assign(array(
            'title'     => _a('Application List'),
            'apps'      => $resultset,
            'paginator' => $paginator,
        ));
    }
    
    public function addAction()
    {
        $form = $this->getApplicationForm('add');
        $this->view()->setTemplate('application-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new AppEditFilter);
            $form->setValidationGroup($this->columns);
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    _a('There are some error occurred.')
                );
            }
            
            $data = $form->getData();
            $result = Pi::api('doc', $this->getModule())->addApplication($data);
            if (!$result) {
                return $this->jumpTo404(_a('Cannot save data.'));
            }
            
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
        
        $appkey = $this->params('appkey', '');
        if (empty($appkey)) {
            return $this->jumpTo404(_a('Invalid application key.'));
        }

        $form->setData(array('appkey' => $appkey));
        
        $this->view()->assign(array(
            'form'  => $form,
        ));
    }
    
    /**
     * Application edit page
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        $id   = $this->params('id', 0);
        $row  = $this->getModel('application')->find($id);
        if (!$row) {
            $this->view()->assign('id', $row->id);
            return;
        }
        
        $form = $this->getApplicationForm('edit');
        $form->setData($row->toArray());
        
        $this->view()->assign(array(
            'form'      => $form,
            'id'        => $row->id,
        ));
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new AppEditFilter);
            $form->setValidationGroup($this->columns);
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    _a('There are some error occurred.')
                );
            }
            
            $data = $form->getData();
            $result = $this->getModel('application')->update(
                array('title' => $data['title']),
                array('id' => $data['id'])
            );
            if ($row->title != $data['title'] 
                && !$result
            ) {
                return $this->renderForm(
                    $form,
                    _a('Cannot save data.')
                );
            }
            
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
    }
    
    /**
     * Delete useless application
     * 
     * @return string
     * @throws \Exception
     */
    public function deleteAction()
    {
        $from   = $this->params('from', '');
        
        $appkey  = $this->params('appkey', 0);
        $appkeys = array_filter(explode(',', $appkey));

        if (empty($appkeys)) {
            throw new \Exception(_a('Invalid application.'));
        }
        
        // Checking if application is in used
        $rowDoc = $this->getModel('doc')
            ->select(array('appkey' => $appkeys));
        if (count($rowDoc) > 0) {
            throw new \Exception(_a('Application already in use.'));
        }
        
        // Removing application
        $this->getModel('application')->delete(array('appkey' => $appkeys));
        
        // Go to list page or original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
    }
}
