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
use Module\Article\Form\ClusterEditForm;
use Module\Article\Form\ClusterEditFilter;
use Module\Article\Model\Cluster;

/**
 * Cluster controller
 * 
 * Feature list:
 * 
 * 1. List/add/edit/delete cluster
 * 2. Merge/move a cluster to another cluster
 * 3. AJAX action for saving cluster image
 * 4. AJAX action for deleting cluster image
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ClusterController extends CategoryController
{
    /**
     * Add cluster information
     * 
     * @return ViewModel 
     */
    public function addAction()
    {
        $parent = $this->params('parent', 0);

        $form   = $this->getClusterForm('add');
        if ($parent) {
            $form->get('parent')->setAttribute('value', $parent);
        }
        $form->setData(array('fake_id' => uniqid()));

        $module  = $this->getModule();
        $configs = Pi::config('', $module);
        $configs['max_media_size'] = Pi::service('file')
            ->transformSize($configs['max_media_size']);
        
        $this->view()->assign(array(
            'title'   => _a('Add Cluster Info'),
            'configs' => $configs,
            'form'    => $form,
        ));
        $this->view()->setTemplate('category-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new ClusterEditFilter);
            $form->setValidationGroup(Cluster::getAvailableFields());
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('There are some error occured!')
                );
                return;
            }
            
            $data = $form->getData();
            $id   = $this->saveCluster($data);
            if (!$id) {
                $this->renderForm(
                    $form,
                    _a('Can not save data!')
                );
                return;
            }
            
            // Clear cache
            Pi::registry('cluster', $module)->clear($module);
            
            return $this->redirect()->toRoute('',array(
                'action' => 'list'
            ));
        }
    }

    /**
     * Edit cluster information
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        $module  = $this->getModule();
        $configs = Pi::config('', $module);
        $configs['max_media_size'] = Pi::service('file')
            ->transformSize($configs['max_media_size']);
        
        $form = $this->getClusterForm('edit');
        
        $this->view()->assign(array(
            'title'   => _a('Edit Cluster Info'),
            'configs' => $configs,
        ));
        $this->view()->setTemplate('category-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $options = array(
                'id' => $post['id'],
            );
            $form->setInputFilter(new ClusterEditFilter($options));
            $form->setValidationGroup(Cluster::getAvailableFields());
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('Can not update data!')
                );
                return;
            }
            $data = $form->getData();
            $id   = $this->saveCluster($data);
            if (empty($id)) {
                return ;
            }
            
            // Clear cache
            Pi::registry('cluster', $module)->clear($module);

            return $this->redirect()->toRoute('', array(
                'action' => 'list'
            ));
        }
        
        $id = $this->params('id', 0);
        if (empty($id)) {
            $this->jumpto404(_a('Invalid cluster ID!'));
        }

        $model = $this->getModel('cluster');
        $row   = $model->find($id);
        if (!$row->id) {
            return $this->jumpTo404(_a('Can not find cluster!'));
        }
        
        $form->setData($row->toArray());

        $parent = $model->getParentNode($row->id);
        if ($parent) {
            $form->get('parent')->setAttribute('value', $parent['id']);
        }

        $this->view()->assign('form', $form);
    }
    
    /**
     * Delete a cluster
     */
    public function deleteAction()
    {
        $id     = $this->params('id');

        if ($id == 1) {
            return $this->jumpTo404(_a('Root node cannot be deleted.'));
        } else if ($id) {
            $model = $this->getModel('cluster');

            // Check children
            if ($model->hasChildren($id)) {
                return $this->jumpTo404(
                    _a('Cannot remove cluster with children')
                );
            }

            // Check related article
            $linkedArticles = $this->getModel('article')
                ->select(array('cluster' => $id));
            if ($linkedArticles->count()) {
                return $this->jumpTo404(_a('Cannot remove cluster in used'));
            }

            // Delete image
            $row = $model->find($id);
            if ($row && $row->image) {
                unlink(Pi::path($row->image));
            }

            // Remove node
            $model->remove($id);
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('cluster', $module)->clear($module);

            // Go to list page
            return $this->redirect()->toRoute('', array('action' => 'list'));
        } else {
            return $this->jumpTo404(_a('Invalid cluster ID!'));
        }
    }

    /**
     * List all added clusters
     */
    public function listAction()
    {
        $rowset = $this->getModel('cluster')->enumerate(null, null, true);

        $this->view()->assign(array(
            'title'      => _a('Cluster List'),
            'items'      => $rowset,
        ));
        $this->view()->setTemplate('category-list');
    }

    /**
     * Merge source cluster to target cluster
     * 
     * @return ViewModel 
     */
    public function mergeAction()
    {
        $form = $this->getIntegrationForm('merge');
        $this->view()->assign(array(
            'title'  => _a('Merge Cluster'),
            'form'   => $form,
            'action' => 'merge',
        ));
        $this->view()->setTemplate('category-integrate');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
        
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('Can not merge cluster!')
                );
                return;
            }
            $data = $form->getData();

            $model = $this->getModel('cluster');

            // Deny to be merged to self or a child
            $descendant = $model->getDescendantIds($data['from']);
            if (array_search($data['to'], $descendant) !== false) {
                $this->renderForm(
                    $form,
                    _a('Cluster cannot be moved to self or a child!')
                );
                return;
            }

            // Move children node
            $children = $model->getChildrenIds($data['from']);
            foreach ($children as $objective) {
                if (!$model->move($objective, $data['to'])) {
                    $this->renderForm(
                        $form,
                        _a('Move children error.')
                    );
                    return;
                }
            }

            // Change relation between article and cluster
            $this->getModel('article')->update(
                array('cluster' => $data['to']),
                array('cluster' => $data['from'])
            );

            // remove cluster
            $model->remove($data['from']);
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('cluster', $module)->clear($module);

            // Go to list page
            return $this->redirect()->toRoute('', array(
                'action' => 'list'
            ));
        }
        
        $from = $this->params('from', 0);
        $to   = $this->params('to', 0);

        if ($from) {
            $form->get('from')->setAttribute('value', $from);
        }
        if ($to) {
            $form->get('to')->setAttribute('value', $to);
        }
    }

    /**
     * Move source cluster as a child of target cluster
     * 
     * @return ViewModel 
     */
    public function moveAction()
    {
        $form = $this->getIntegrationForm();
        $this->view()->assign(array(
            'title'  => _a('Move Cluster'),
            'form'   => $form,
            'action' => 'move',
        ));
        $this->view()->setTemplate('category-integrate');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);

            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('Can not move cluster!')
                );
                return;
            }
                
            $data = $form->getData();
            $model = $this->getModel('cluster');

            // Deny to be moved to self or a child
            $children = $model->getDescendantIds($data['from']);
            if (array_search($data['to'], $children) !== false) {
                $this->renderForm(
                    $form,
                    _a('Cluster cannot be moved to self or a child!')
                );
                return;
            }

            // Move cluster
            $model->move($data['from'], $data['to']);
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('cluster', $module)->clear($module);

            // Go to list page
            return $this->redirect()->toRoute('', array(
                'action' => 'list'
            ));
        }
        
        $from = $this->params('from', 0);
        $to   = $this->params('to', 0);

        if ($from) {
            $form->get('from')->setAttribute('value', $from);
        }
        if ($to) {
            $form->get('to')->setAttribute('value', $to);
        }
    }
    
    /**
     * Sort cluster
     * 
     * @return ViewModel 
     */
    public function sortAction()
    {
        $from = $this->params('from', 0);
        if (empty($from)) {
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
        
        $model  = $this->getModel('cluster');
        $parent = $model->getParentNode($from);
        $rowset = $model->getChildren($parent['id']);
        $children = array();
        foreach ($rowset as $row) {
            $children[$row->id] = $row->title;
        }
        unset($children[$parent['id']]);
        $form = $this->getSortForm($from, $children);
        $this->view()->assign(array(
            'title' => _a('Sort Cluster'),
            'form'  => $form,
        ));
        $this->view()->setTemplate('category-sort');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('Can not sort cluster!')
                );
                return;
            }
                
            $data = $form->getData();
            $model = $this->getModel('cluster');

            // Deny to move item to other cluster
            if (!empty($data['to']) 
                && !in_array($data['to'], array_keys($children))
            ) {
                $this->renderForm(
                    $form,
                    _a('Cluster cannot be moved to another cluster!')
                );
                return;
            }

            // Sort cluster
            if (empty($data['to'])) {
                $model->move($data['from'], $parent['id'], 'firstOf');
            } else {
                $model->move($data['from'], $data['to'], 'nextTo');
            }
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('cluster', $module)->clear($module);

            // Go to list page
            return $this->redirect()->toRoute('', array(
                'action' => 'list'
            ));
        }
        
        $to   = $this->params('to', 0);
        if ($to) {
            $form->get('to')->setAttribute('value', $to);
        }
    }
    
    /**
     * Get cluster form object
     * 
     * @param string $action  Form name
     * @return ClusterEditForm
     */
    protected function getClusterForm($action = 'add')
    {
        $form = new ClusterEditForm();
        $form->setAttribute('action', $this->url('', array('action' => $action)));

        return $form;
    }

    /**
     * Save cluster information
     * 
     * @param  array    $data  Cluster information
     * @return boolean
     * @throws \Exception 
     */
    protected function saveCluster($data)
    {
        $model  = $this->getModel('cluster');

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        
        $parent = $data['parent'];
        unset($data['parent']);

        if (isset($data['slug']) && empty($data['slug'])) {
            unset($data['slug']);
        }

        if (empty($id)) {
            $id = $model->add($data, $parent);
            $row = $model->find($id);
        } else {
            $row = $model->find($id);

            if (empty($row)) {
                return $this->jumpTo404(_a('Cluster is not exists.'));
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
                        _a('Cluster cannot be moved to self or a child.')
                    );
                } else {
                    $model->move($id, $parent);
                }
            }
        }
        
        return $id;
    }
    
    /**
     * Get move or merge form instance
     * 
     * @param string $mode
     * @return \Pi\Form\Form
     */
    protected function getIntegrationForm($mode = 'move')
    {
        $form = new \Pi\Form\Form;
        $type = ('move' === $mode ? 'Module\Article\Form\Element\ClusterWithRoot'
            : 'Module\Article\Form\Element\Cluster');
        $elements = array(
            array(
                'name'       => 'from',
                'options'    => array(
                    'label'     => __('From'),
                ),
                'attributes' => array(
                    'id'        => 'from',
                    'class'     => 'form-control',
                ),
                'type'       => 'Module\Article\Form\Element\Cluster',
            ),
            array(
                'name'       => 'to',
                'options'    => array(
                    'label'     => __('To'),
                ),
                'attributes' => array(
                    'id'        => 'to',
                    'class'     => 'form-control',
                ),
                'type'       => $type,
            ),
            array(
                'name'       => 'security',
                'type'       => 'csrf',
            ),
            array(
                'name'       => 'submit',
                'attributes' => array(              
                    'value'     => __('Submit'),
                ),
                'type'       => 'submit',
            ),
        );
        foreach ($elements as $element) {
            $form->add($element);
        }
        
        $filters = array(
            array(
                'name'     => 'from',
                'required' => true,
            ),
            array(
                'name'     => 'to',
                'required' => 'move' === $mode ? false : true,
            ),
        );
        $filter = new \Zend\InputFilter\InputFilter;
        foreach ($filters as $element) {
            $filter->add($element);
        }
        $form->setInputFilter($filter);
        
        return $form;
    }
}
