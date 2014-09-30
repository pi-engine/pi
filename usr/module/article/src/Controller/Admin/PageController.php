<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Page controller
 * 
 * Set page SEO details
 * Enabled page dressup respectively
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class PageController extends ActionController
{
    /**
     * Default page, redirect to list page
     * 
     * @return ViewModel 
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array(
            'action'     => 'list',
        ));
    }
    
    /**
     * List pages
     * 
     * @return ViewModel
     */
    public function listAction()
    {
        $rowset = $this->getModel('page')->enumerate(null, null, true);
        
        $module   = $this->getModule();
        $category = Pi::api('api', $module)->getCategoryList();
        $cluster  = Pi::api('api', $module)->getClusterList();

        $this->view()->assign(array(
            'title'      => _a('Page List'),
            'items'      => $rowset,
            'category'   => $category,
            'cluster'    => $cluster,
        ));
    }
    
    /**
     * Add page
     * 
     * @return ViewModel
     */
    public function addAction()
    {
        $parent = $this->params('parent', 0);
        
        $module = $this->getModule();
        $form = Pi::api('page', $module)->loadForm('form', true);
        if ($parent) {
            $form->get('parent')->setValue($parent);
        }
        
        $this->view()->assign(array(
            'form'  => $form,
        ));
        $this->view()->setTemplate('page-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('There are some error occured!')
                );
                return;
            }
            $data = $form->getData();
            
            // Check if page is allowed to dress up
            $rule = $this->getRule($data);
            if (empty($rule)) {
                $this->renderForm(
                    $form,
                    _a('This page is not allowed to dress up.')
                );
                return;
            }
            
            // Save data
            $data = array_merge($data, $rule);
            $id   = $this->save($data);
            if (!$id) {
                $this->renderForm(
                    $form,
                    _a('Can not save data!')
                );
                return;
            }
            
            // Clear cache
            Pi::registry('page', $module)->clear($module);
            
            return $this->redirect()->toRoute('',array(
                'action' => 'list'
            ));
        }
    }
    
    /**
     * Edit page
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        $module  = $this->getModule();
        
        $form = Pi::api('page', $module)->loadForm('form', true);
        $this->view()->assign(array(
            'form'  => $form,
        ));
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('There are some error occured!')
                );
                return;
            }
            $data = $form->getData();
            
            // Check if page is allowed to dress up
            $rule = $this->getRule($data);
            if (empty($rule)) {
                $this->renderForm(
                    $form,
                    _a('This page is not allowed to dress up.')
                );
                return;
            }
            
            // Save data
            $data = array_merge($data, $rule);
            $id   = $this->save($data);
            if (!$id) {
                $this->renderForm(
                    $form,
                    _a('Can not update data!')
                );
                return;
            }
            
            // Clear cache
            Pi::registry('page', $module)->clear($module);

            return $this->redirect()->toRoute('', array(
                'action' => 'list'
            ));
        }
        
        $id = $this->params('id', 0);
        if (empty($id)) {
            $this->jumpto404(_a('Invalid page ID!'));
        }

        $model = $this->getModel('page');
        $row   = $model->find($id);
        if (!$row->id) {
            return $this->jumpTo404(_a('Can not find page!'));
        }
        
        $meta = json_decode($row->meta, true);
        $data = array_merge($row->toArray(), $meta);
        unset($data['meta']);
        $form->setData($data);

        $parent = $model->getParentNode($row->id);
        if ($parent) {
            $form->get('parent')->setAttribute('value', $parent['id']);
        }
    }
    
    public function deleteAction()
    {
        $id     = $this->params('id');

        if ($id) {
            $model = $this->getModel('page');

            // Check children
            if ($model->hasChildren($id)) {
                return $this->jumpTo404(
                    _a('Cannot remove page with children.')
                );
            }

            // Remove node
            $model->remove($id);
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('page', $module)->clear($module);

            // Go to list page
            return $this->redirect()->toRoute('', array('action' => 'list'));
        } else {
            return $this->jumpTo404(_a('Invalid page ID!'));
        }
    }
    
    /**
     * Render form
     * 
     * @param Form      $form     Form instance
     * @param string    $message  Message assign to template
     * @param bool      $error    Whether is error message
     */
    public function renderForm(Form $form, $message = null, $error = true)
    {
        $params = compact('form', 'message', 'error');
        $this->view()->assign($params);
    }
    
    /**
     * Save category information
     * 
     * @param  array    $data  Category information
     * @return boolean
     * @throws \Exception 
     */
    protected function save($data)
    {
        $model  = $this->getModel('page');

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        
        $parent = $data['parent'];
        unset($data['parent']);
        unset($data['submit']);
        unset($data['security']);
        $meta    = array();
        $columns = $model->getColumns(true);
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $columns)) {
                $meta[$key] = $data[$key];
                unset($data[$key]);
            }
        }
        $data['meta'] = json_encode($meta);

        if (empty($id)) {
            $id = $model->add($data, $parent);
            $row = $model->find($id);
        } else {
            $row = $model->find($id);

            if (empty($row)) {
                return $this->jumpTo404(_a('Page is not exists.'));
            }
            
            $row->assign($data);
            $row->save();

            // Move node position
            $parentNode    = $model->getParentNode($id);
            $currentParent = $parentNode['id'];
            if ($currentParent != $parent) {
                $children = $model->getDescendantIds($id);
                if (array_search($parent, $children) !== false) {
                    return $this->jumpTo404(
                        _a('Category cannot be moved to self or a child.')
                    );
                } else {
                    $model->move($id, $parent);
                }
            }
        }

        return $id;
    }
    
    /**
     * Get rule by post data
     * 
     * @param array $data
     * @return array
     */
    protected function getRule($data)
    {
        $conditions = array();
        
        $module     = $this->getModule();
        $conditionForms = Pi::api('page', $module)->getConditionForm();
        foreach ($conditionForms as $name => $level) {
            if (empty($data[$name])) {
                continue;
            }
            if ('value' === $level) {
                $conditions[$name] = $data[$name];
            } else {
                $conditions[$name] = '';
            }
        }
        $conditionKey = Pi::api('page', $module)->searchConditionName($conditions);
        $rule = Pi::api('page', $module)->getRule($conditionKey);
        
        return $rule;
    }
}
