<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Pi;
use Pi\Paginator\Paginator;
use Module\Article\Form\CategoryEditForm;
use Module\Article\Form\CategoryEditFilter;
use Module\Article\Form\CategoryMergeForm;
use Module\Article\Form\CategoryMergeFilter;
use Module\Article\Form\CategoryMoveForm;
use Module\Article\Form\CategoryMoveFilter;
use Module\Article\Model\Category;
use Zend\Db\Sql\Expression;
use Module\Article\Service;
use Module\Article\Model\Article;
use Module\Article\Entity;
use Pi\File\Transfer\Upload as UploadHandler;

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
     * Get category form object
     * 
     * @param string $action  Form name
     * @return \Module\Article\Form\CategoryEditForm 
     */
    protected function getCategoryForm($action = 'add')
    {
        $form = new CategoryEditForm();
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'class'   => 'form-horizontal',
        ));

        return $form;
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
        $module        = $this->getModule();
        $modelCategory = $this->getModel('category');
        $fakeId        = $image = null;

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }

        $fakeId = Service::getParam($this, 'fake_id', 0);

        unset($data['image']);

        $parent = $data['parent'];
        unset($data['parent']);

        if (isset($data['slug']) && empty($data['slug'])) {
            unset($data['slug']);
        }

        if (empty($id)) {
            $id = $modelCategory->add($data, $parent);
            $rowCategory = $modelCategory->find($id);
        } else {
            $rowCategory = $modelCategory->find($id);

            if (empty($rowCategory)) {
                Service::jumpToErrorOperation(
                    $this,
                    _a('Category is not exists.')
                );
                return false;
            }

            $rowCategory->assign($data);
            $rowCategory->save();

            // Move node position
            $parentNode    = $modelCategory->getParentNode($id);
            $currentParent = $parentNode['id'];
            if ($currentParent != $parent) {
                $children = $modelCategory->getDescendantIds($id);
                if (array_search($parent, $children) !== false) {
                    Service::jumpToErrorOperation(
                        $this,
                        _a('Category cannot be moved to self or a child.')
                    );
                    return false;
                } else {
                    $modelCategory->move($id, $parent);
                }
            }
        }

        // Save image
        $session    = Service::getUploadSession($module, 'category');
        if (isset($session->$id)
            || ($fakeId && isset($session->$fakeId))
        ) {
            $uploadInfo = isset($session->$id)
                ? $session->$id : $session->$fakeId;

            if ($uploadInfo) {
                $fileName = $rowCategory->id;

                $pathInfo = pathinfo($uploadInfo['tmp_name']);
                if ($pathInfo['extension']) {
                    $fileName .= '.' . $pathInfo['extension'];
                }
                $fileName = $pathInfo['dirname'] . '/' . $fileName;

                $rowCategory->image = rename(
                    Pi::path($uploadInfo['tmp_name']),
                    Pi::path($fileName)
                ) ? $fileName : $uploadInfo['tmp_name'];
                $rowCategory->save();
            }

            unset($session->$id);
            unset($session->$fakeId);
        }

        return $id;
    }
    
    /**
     * Category index page, which will redirect to category article list page
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

        $form->setData(array('fake_id'  => uniqid()));

        Service::setModuleConfig($this);
        $this->view()->assign(array(
            'title'                 => _a('Add Category Info'),
            'form'                  => $form,
        ));
        $this->view()->setTemplate('category-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new CategoryEditFilter);
            $form->setValidationGroup(Category::getAvailableFields());
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('There are some error occured!')
                );
            }
            
            $data = $form->getData();
            $id   = $this->saveCategory($data);
            if (!$id) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('Can not save data!')
                );
            }
            
            // Clear cache
            $module = $this->getModule();
            Pi::service('registry')
                ->handler('category', $module)
                ->clear($module);
            
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list')
            );
        }
    }

    /**
     * Edit category information
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        Service::setModuleConfig($this);
        $this->view()->assign('title', _a('Edit Category Info'));
        
        $form = $this->getCategoryForm('edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $options = array(
                'id' => $post['id'],
            );
            $form->setInputFilter(new CategoryEditFilter($options));
            $form->setValidationGroup(Category::getAvailableFields());
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('Can not update data!')
                );
            }
            $data = $form->getData();
            $id   = $this->saveCategory($data);
            if (empty($id)) {
                return ;
            }
            
            // Clear cache
            $module = $this->getModule();
            Pi::service('registry')
                ->handler('category', $module)
                ->clear($module);

            return $this->redirect()->toRoute(
                '',
                array('action' => 'list')
            );
        }
        
        $id     = $this->params('id', 0);
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
     * Delete a category
     */
    public function deleteAction()
    {
        $id     = $this->params('id');

        if ($id == 1) {
            return Service::jumpToErrorOperation(
                $this,
                _a('Root node cannot be deleted.')
            );
        } else if ($id) {
            $categoryModel = $this->getModel('category');

            // Check default category
            if ($this->config('default_category') == $id) {
                return Service::jumpToErrorOperation(
                    $this,
                    _a('Cannot remove default category')
                );
            }

            // Check children
            if ($categoryModel->hasChildren($id)) {
                return Service::jumpToErrorOperation(
                    $this,
                    _a('Cannot remove category with children')
                );
            }

            // Check related article
            $linkedArticles = $this->getModel('article')
                ->select(array('category' => $id));
            if ($linkedArticles->count()) {
                return Service::jumpToErrorOperation(
                    $this,
                    _a('Cannot remove category in used')
                );
            }

            // Delete image
            $row = $categoryModel->find($id);
            if ($row && $row->image) {
                unlink(Pi::path($row->image));
            }

            // Remove node
            $categoryModel->remove($id);
            
            // Clear cache
            $module = $this->getModule();
            Pi::service('registry')
                ->handler('category', $module)
                ->clear($module);

            // Go to list page
            $this->redirect()->toRoute('', array('action' => 'list'));
            $this->view()->setTemplate(false);
        } else {
            return $this->jumpTo404(_a('Invalid category ID!'));
        }
    }

    /**
     * List all added categories
     */
    public function listAction()
    {
        $model = $this->getModel('category');
        $rowset = $model->enumerate(null, null, true);

        $this->view()->assign('categories', $rowset);
        $this->view()->assign('title', _a('Category List'));
        $this->view()->assign(
            'defaultLogo',
            Pi::service('asset')
                ->getModuleAsset('image/default-category-thumb.png', $module)
        );
    }

    /**
     * Merge source category to target category
     * 
     * @return ViewModel 
     */
    public function mergeAction()
    {
        $form = new CategoryMergeForm();
        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Merge Category'));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new CategoryMergeFilter);
        
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('Can not merge category!')
                );
            }
            $data = $form->getData();

            $categoryModel = $this->getModel('category');

            // Deny to be merged to self or a child
            $descendant = $categoryModel->getDescendantIds($data['from']);
            if (array_search($data['to'], $descendant) !== false) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('Category cannot be moved to self or a child!')
                );
            }

            // From node cannot be default
            if ($this->config('default_category') == $data['from']) {
               return Service::renderForm(
                   $this,
                   $form,
                   _a('Cannot merge default category')
               );
            }

            // Move children node
            $children = $categoryModel->getChildrenIds($data['from']);
            foreach ($children as $objective) {
                if (!$categoryModel->move($objective, $data['to'])) {
                    return Service::renderForm(
                        $this,
                        $form,
                        _a('Move children error.')
                    );
                }
            }

            // Change relation between article and category
            $this->getModel('article')->update(
                array('category' => $data['to']),
                array('category' => $data['from'])
            );

            // remove category
            $categoryModel->remove($data['from']);
            
            // Clear cache
            $module = $this->getModule();
            Pi::service('registry')
                ->handler('category', $module)
                ->clear($module);

            // Go to list page
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list')
            );
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
        $form = new CategoryMoveForm();
        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Move Category'));
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new CategoryMoveFilter);

            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('Can not move category!')
                );
            }
                
            $data = $form->getData();
            $categoryModel = $this->getModel('category');

            // Deny to be moved to self or a child
            $children = $categoryModel->getDescendantIds($data['from']);
            if (array_search($data['to'], $children) !== false) {
                return Service::renderForm(
                    $this,
                    $form,
                    _a('Category cannot be moved to self or a child!')
                );
            }

            // Move category
            $categoryModel->move($data['from'], $data['to']);
            
            // Clear cache
            $module = $this->getModule();
            Pi::service('registry')
                ->handler('category', $module)
                ->clear($module);

            // Go to list page
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list')
            );
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
     * Save image by AJAX, but do not save data into database.
     * If the image is fetched by upload, try to receive image by Upload class,
     * if the it comes from media, copy the image from media to category path.
     * Finally the image data will be saved into session.
     * 
     */
    public function saveImageAction()
    {
        Pi::service('log')->active(false);
        $module  = $this->getModule();

        $return  = array('status' => false);
        $mediaId = Service::getParam($this, 'media_id', 0);
        $id      = Service::getParam($this, 'id', 0);
        if (empty($id)) {
            $id = Service::getParam($this, 'fake_id', 0);
        }
        // Check is id valid
        if (empty($id)) {
            $return['message'] = _a('Invalid ID!');
            echo json_encode($return);
            exit;
        }
        
        $extensions = array_filter(
            explode(',', $this->config('image_extension'))
        );
        foreach ($extensions as &$ext) {
            $ext = strtolower(trim($ext));
        }
        
        // Get destination path
        $destination = Service::getTargetDir('category', $module, true, false);

        if ($mediaId) {
            $rowMedia = $this->getModel('media')->find($mediaId);
            // Check is media exists
            if (!$rowMedia->id or !$rowMedia->url) {
                $return['message'] = _a('Media is not exists!');
                echo json_encode($return);
                exit;
            }
            // Check is media an image
            if (!in_array(strtolower($rowMedia->type), $extensions)) {
                $return['message'] = _a('Invalid file extension!');
                echo json_encode($return);
                exit;
            }
            
            $ext    = strtolower(pathinfo($rowMedia->url, PATHINFO_EXTENSION));
            $rename = $id . '.' . $ext;
            $fileName = rtrim($destination, '/') . '/' . $rename;
            if (!copy(Pi::path($rowMedia->url), Pi::path($fileName))) {
                $return['message'] = _a('Can not create image file!');
                echo json_encode($return);
                exit;
            }
        } else {
            $rawInfo = $this->request->getFiles('upload');

            $ext = strtolower(pathinfo($rawInfo['name'], PATHINFO_EXTENSION));
            $rename = $id . '.' . $ext;

            $upload = new UploadHandler;
            $upload->setDestination(Pi::path($destination))
                   ->setRename($rename)
                   ->setExtension($this->config('image_extension'))
                   ->setSize($this->config('max_image_size'));

            // Checking is uploaded file valid
            if (!$upload->isValid()) {
                $return['message'] = $upload->getMessages();
                echo json_encode($return);
                exit;
            }
            
            $upload->receive();
            $fileName = $destination . '/' . $rename;
        }

        // Scale image
        $uploadInfo['tmp_name'] = $fileName;
        $uploadInfo['w']        = $this->config('category_width');
        $uploadInfo['h']        = $this->config('category_height');

        Service::saveImage($uploadInfo);

        // Save image to category
        $rowCategory = $this->getModel('category')->find($id);
        if ($rowCategory) {
            if ($rowCategory->image && $rowCategory->image != $fileName) {
                @unlink(Pi::path($rowCategory->image));
            }

            $rowCategory->image = $fileName;
            $rowCategory->save();
        } else {
            // Or save info to session
            $session = Service::getUploadSession($module, 'category');
            $session->$id = $uploadInfo;
        }

        $imageSize = getimagesize(Pi::path($fileName));
        $orginalName = isset($rawInfo['name']) ? $rawInfo['name'] : $rename;

        // Prepare return data
        $return['data'] = array(
            'originalName' => $orginalName,
            'size'         => filesize(Pi::path($fileName)),
            'w'            => $imageSize['0'],
            'h'            => $imageSize['1'],
            'preview_url'  => Pi::url($fileName),
            'filename'     => $fileName,
        );

        $return['status'] = true;
        echo json_encode($return);
        exit();
    }
    
    /**
     * Removing image by AJAX.
     * This operation will also remove image data in database.
     * 
     * @return ViewModel 
     */
    public function removeImageAction()
    {
        Pi::service('log')->active(false);
        $id           = Service::getParam($this, 'id', 0);
        $fakeId       = Service::getParam($this, 'fake_id', 0);
        $affectedRows = 0;
        $module       = $this->getModule();

        if ($id) {
            $rowCategory = $this->getModel('category')->find($id);

            if ($rowCategory && $rowCategory->image) {
                // Delete image
                @unlink(Pi::path($rowCategory->image));

                // Update db
                $rowCategory->image = '';
                $affectedRows       = $rowCategory->save();
            }
        } else if ($fakeId) {
            $session = Service::getUploadSession($module, 'category');

            if (isset($session->$fakeId)) {
                $uploadInfo = isset($session->$id)
                    ? $session->$id : $session->$fakeId;

                @unlink(Pi::path($uploadInfo['tmp_name']));

                unset($session->$id);
                unset($session->$fakeId);
            }
        }

        return array(
            'status'    => $affectedRows ? true : false,
            'message'   => 'ok',
        );
    }
}
