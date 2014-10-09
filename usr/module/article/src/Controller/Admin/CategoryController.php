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
use Pi\Form\Form;
use Pi\Mvc\Controller\ActionController;
use Module\Article\Form\CategoryEditForm;
use Module\Article\Form\CategoryEditFilter;

/**
 * Category controller
 * 
 * Feature list:
 * 
 * 1. List/add/edit/delete category
 * 2. Merge/move a category to another category
 * 3. AJAX action for saving category image
 * 4. AJAX action for deleting category image
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CategoryController extends ActionController
{
    /**
     * Category index page, which will redirect to category list page
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute(
            '',
            array('action' => 'list')
        );
    }
    
    /**
     * Add category information
     * 
     * @return ViewModel 
     */
    public function addAction()
    {
        $parent = $this->params('parent', 0);

        $form   = $this->getCategoryForm('add');
        if ($parent) {
            $form->get('parent')->setAttribute('value', $parent);
        }
        $form->setData(array('fake_id' => uniqid()));

        $module  = $this->getModule();
        $configs = Pi::config('', $module);
        $configs['max_media_size'] = Pi::service('file')
            ->transformSize($configs['max_media_size']);
        
        $this->view()->assign(array(
            'title'   => _a('Add Category Info'),
            'configs' => $configs,
            'form'    => $form,
        ));
        $this->view()->setTemplate('category-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new CategoryEditFilter);
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('There are some error occured!')
                );
                return;
            }
            
            $data = $form->getData();
            $id   = $this->saveCategory($data);
            if (!$id) {
                $this->renderForm(
                    $form,
                    _a('Can not save data!')
                );
                return;
            }
            
            // Clear cache
            Pi::registry('category', $module)->clear($module);
            
            return $this->redirect()->toRoute('',array(
                'action' => 'list'
            ));
        }
    }

    /**
     * Edit category information
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        $module  = $this->getModule();
        $configs = Pi::config('', $module);
        $configs['max_media_size'] = Pi::service('file')
            ->transformSize($configs['max_media_size']);
        
        $form = $this->getCategoryForm('edit');
        
        $this->view()->assign(array(
            'title'   => _a('Edit Category Info'),
            'configs' => $configs,
        ));
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $options = array(
                'id' => $post['id'],
            );
            $form->setInputFilter(new CategoryEditFilter($options));
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('Can not update data!')
                );
                return;
            }
            $data = $form->getData();
            $id   = $this->saveCategory($data);
            if (empty($id)) {
                return ;
            }
            
            // Clear cache
            Pi::registry('category', $module)->clear($module);

            return $this->redirect()->toRoute('', array(
                'action' => 'list'
            ));
        }
        
        $id = $this->params('id', 0);
        if (empty($id)) {
            $this->jumpto404(_a('Invalid category ID!'));
        }

        $model = $this->getModel('category');
        $row   = $model->find($id);
        if (!$row->id) {
            return $this->jumpTo404(_a('Can not find category!'));
        }
        
        $form->setData($row->toArray());

        $parent = $model->getParentNode($row->id);
        if ($parent) {
            $form->get('parent')->setAttribute('value', $parent['id']);
        }

        $this->view()->assign('form', $form);
    }
    
    /**
     * Active/deactivate category
     * 
     * @return ViewModel
     */
    public function activeAction()
    {
        $status = $this->params('status', 0);
        $id     = $this->params('id', 0);
        
        $module = $this->getModule();
        $model  = $this->getModel('category');
        $children = $model->getChildrenIds($id);
        $children[] = $id;

        $model->update(
            array('active' => $status),
            array('id' => $children)
        );

        // Clear cache
        Pi::registry('category', $module)->clear($module);
        
        return $this->redirect()->toRoute('', array(
            'action' => 'list'
        ));
    }
    
    /**
     * Delete a category
     */
    public function deleteAction()
    {
        $id     = $this->params('id');

        if ($id == 1) {
            return $this->jumpTo404(_a('Root node cannot be deleted.'));
        } else if ($id) {
            $model = $this->getModel('category');

            // Check default category
            if ($this->config('default_category') == $id) {
                return $this->jumpTo404(_a('Cannot remove default category'));
            }

            // Check children
            if ($model->hasChildren($id)) {
                return $this->jumpTo404(
                    _a('Cannot remove category with children')
                );
            }

            // Check related article
            $linkedArticles = $this->getModel('article')
                ->select(array('category' => $id));
            if ($linkedArticles->count()) {
                return $this->jumpTo404(_a('Cannot remove category in used'));
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
            Pi::registry('category', $module)->clear($module);

            // Go to list page
            return $this->redirect()->toRoute('', array('action' => 'list'));
        } else {
            return $this->jumpTo404(_a('Invalid category ID!'));
        }
    }

    /**
     * List all added categories
     */
    public function listAction()
    {
        $rowset = $this->getModel('category')->enumerate(null, null, true);

        $this->view()->assign(array(
            'title'      => _a('Category List'),
            'items'      => $rowset,
        ));
    }

    /**
     * Merge source category to target category
     * 
     * @return ViewModel 
     */
    public function mergeAction()
    {
        $form = $this->getIntegrationForm('merge');
        $this->view()->assign(array(
            'title'  => _a('Merge Category'),
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
                    _a('Can not merge category!')
                );
                return;
            }
            $data = $form->getData();

            $model = $this->getModel('category');

            // Deny to be merged to self or a child
            $descendant = $model->getDescendantIds($data['from']);
            if (array_search($data['to'], $descendant) !== false) {
                $this->renderForm(
                    $form,
                    _a('Category cannot be moved to self or a child!')
                );
                return;
            }

            // From node cannot be default
            if ($this->config('default_category') == $data['from']) {
                $this->renderForm(
                    $form,
                    _a('Cannot merge default category')
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

            // Change relation between article and category
            $this->getModel('article')->update(
                array('category' => $data['to']),
                array('category' => $data['from'])
            );

            // remove category
            $model->remove($data['from']);
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('category', $module)->clear($module);

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
     * Move source category as a child of target category
     * 
     * @return ViewModel 
     */
    public function moveAction()
    {
        $form = $this->getIntegrationForm();
        $this->view()->assign(array(
            'title'  => _a('Move Category'),
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
                    _a('Can not move category!')
                );
                return;
            }
                
            $data = $form->getData();
            $model = $this->getModel('category');

            // Deny to be moved to self or a child
            $children = $model->getDescendantIds($data['from']);
            if (array_search($data['to'], $children) !== false) {
                $this->renderForm(
                    $form,
                    _a('Category cannot be moved to self or a child!')
                );
                return;
            }

            // Move category
            $model->move($data['from'], $data['to']);
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('category', $module)->clear($module);

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
     * Sort category
     * 
     * @return ViewModel 
     */
    public function sortAction()
    {
        $from = $this->params('from', 0);
        if (empty($from)) {
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
        
        $model  = $this->getModel('category');
        $parent = $model->getParentNode($from);
        $rowset = $model->getChildren($parent['id']);
        $children = array();
        foreach ($rowset as $row) {
            $children[$row->id] = $row->title;
        }
        unset($children[$parent['id']]);
        $form = $this->getSortForm($from, $children);
        $this->view()->assign(array(
            'title' => _a('Sort Category'),
            'form'  => $form,
        ));
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            if (!$form->isValid()) {
                $this->renderForm(
                    $form,
                    _a('Can not sort category!')
                );
                return;
            }
                
            $data = $form->getData();
            $model = $this->getModel('category');

            // Deny to move item to other category
            if (!empty($data['to']) 
                && !in_array($data['to'], array_keys($children))
            ) {
                $this->renderForm(
                    $form,
                    _a('Category cannot be moved to another category!')
                );
                return;
            }

            // Sort category
            if (empty($data['to'])) {
                $model->move($data['from'], $parent['id'], 'firstOf');
            } else {
                $model->move($data['from'], $data['to'], 'nextTo');
            }
            
            // Clear cache
            $module = $this->getModule();
            Pi::registry('category', $module)->clear($module);

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
     * Get category form object
     * 
     * @param string $action  Form name
     * @return CategoryEditForm
     */
    protected function getCategoryForm($action = 'add')
    {
        $form = new CategoryEditForm();
        $form->setAttribute('action', $this->url('', array('action' => $action)));

        return $form;
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
    protected function saveCategory($data)
    {
        $model  = $this->getModel('category');

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        
        $parent = $data['parent'];
        unset($data['parent']);

        if (isset($data['slug']) && empty($data['slug'])) {
            unset($data['slug']);
        }
        
        $data = $model->canonizeColumns($data);

        if (empty($id)) {
            $id = $model->add($data, $parent);
            $row = $model->find($id);
        } else {
            $row = $model->find($id);

            if (empty($row)) {
                return $this->jumpTo404(_a('Category is not exists.'));
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
        
        // Active/deactivate subcategories
        $children = $model->getChildrenIds($id);
        if (!empty($children)) {
            $model->update(
                array('active' => $data['active']),
                array('id' => $children)
            );
        }

        return $id;
    }
    
    /**
     * Get sort form instance
     * 
     * @param int $from
     * @param array $sibling
     *
     * @return Form
     */
    protected function getSortForm($from, array $sibling)
    {
        $name = $sibling[$from];
        unset($sibling[$from]);
        $form = new Form;
        $elements = array(
            array(
                'name'       => 'from',
                'options'    => array(
                    'label'     => __('From'),
                ),
                'attributes' => array(
                    'class'     => 'form-control',
                    'options'   => array(
                        $from         => $name,
                    ),
                ),
                'type'       => 'select',
            ),
            array(
                'name'       => 'to',
                'options'    => array(
                    'label'     => __('To'),
                ),
                'attributes' => array(
                    'class'     => 'form-control',
                    'options'   => array(0 => __('First of')) + $sibling,
                ),
                'type'       => 'select',
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
        
        return $form;
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
                'type'       => 'Module\Article\Form\Element\Category',
            ),
            array(
                'name'       => 'to',
                'options'    => array(
                    'label'     => __('To'),
                    'root'      => 'move' === $mode,
                ),
                'attributes' => array(
                    'id'        => 'to',
                    'class'     => 'form-control',
                ),
                'type'       => 'Module\Article\Form\Element\Category',
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
