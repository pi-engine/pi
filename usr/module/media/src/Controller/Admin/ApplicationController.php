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
use Module\Media\Form\AppEditForm;
use Module\Media\Form\AppEditFilter;
use Module\Media\Model\Application;
use Zend\Db\Sql\Expression;
use Pi\Paginator\Paginator;
use Module\Media\Service;
use Pi;

/**
 * Application controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ApplicationController extends ActionController
{
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

        $module = $this->getModule();
        $model  = $this->getModel('application');
        $select = $model->select();
        if ($name) {
            $select->where->like('name', "%{$name}%");
        }
        $select->order('id ASC')->offset($offset)->limit($limit);

        $resultset = $model->selectWith($select);

        // Total count
        $select = $model->select()->columns(
            array('total' => new Expression('count(id)'))
        );
        if ($name) {
            $select->where->like('name', "%{$name}%");
        }
        $countResultset = $model->selectWith($select);
        $totalCount = intval($countResultset->current()->total);

        // PaginatorPaginator
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'router'    => $this->getEvent()->getRouter(),
                'route'     => $this->getEvent()
                    ->getRouteMatch()
                    ->getMatchedRouteName(),
                'params'    => array_filter(array(
                    'module'        => $module,
                    'controller'    => 'application',
                    'action'        => 'list',
                    'name'          => $name,
                )),
            ));
        
        $this->view()->assign(array(
            'title'     => _a('Application List'),
            'apps'      => $resultset,
            'paginator' => $paginator,
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
            $form->setValidationGroup(Application::getAvailableFields());
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('There are some error occur')
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
                return Service::renderForm(
                    $this,
                    $form,
                    _a('Cannot save data')
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
        
        $id     = $this->params('id', 0);
        $ids    = array_filter(explode(',', $id));

        if (empty($ids)) {
            throw new \Exception(_a('Invalid media IDs'));
        }
        
        // Checking if application is in used
        $rowMedia = $this->getModel('detail')
            ->select(array('application' => $ids));
        if (count($rowMedia) > 0) {
            throw new \Exception(_a('Application already in used'));
        }
        
        // Removing application
        $this->getModel('application')->delete(array('id' => $ids));
        
        // Go to list page or original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
    }
}
