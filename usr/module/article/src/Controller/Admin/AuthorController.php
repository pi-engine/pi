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
use Module\Article\Form\AuthorEditForm;
use Module\Article\Form\AuthorEditFilter;
use Module\Article\Model\Author;
use Zend\Db\Sql\Expression;
use Module\Article\Service;
use Module\Article\Media;
use Pi\File\Transfer\Upload as UploadHandler;

/**
 * Article author controller
 *
 * Feature list:
 * 
 * 1. List/add/edit/delete author
 * 2. AJAX action for saving/removing author photo
 * 3. AJAX action for fuzzy searching author by name
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AuthorController extends ActionController
{
    /**
     * Getting form instance
     * 
     * @param string  $action  Action to request when submit
     * @return \Module\Article\Form\AuthorEditForm 
     */
    protected function getAuthorForm($action = 'add')
    {
        $form = new AuthorEditForm();
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'class'   => 'form-horizontal',
        ));

        return $form;
    }

    /**
     * Save author information
     * 
     * @param array  $data  Author information
     * @return boolean 
     */
    protected function saveAuthor($data)
    {
        $module      = $this->getModule();
        $modelAuthor = $this->getModel('author');
        $fakeId      = $photo = null;

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }

        $fakeId = Service::getParam($this, 'fake_id', 0);

        unset($data['photo']);

        if (empty($id)) {
            $rowAuthor = $modelAuthor->createRow($data);
            $rowAuthor->save();

            if (empty($rowAuthor->id)) {
                return false;
            }

            $id = $rowAuthor->id;
        } else {
            $rowAuthor = $modelAuthor->find($id);

            if (empty($rowAuthor)) {
                return false;
            }

            $rowAuthor->assign($data);
            $rowAuthor->save();
        }

        // Save photo
        $session    = Service::getUploadSession($module, 'author');
        if (isset($session->$id)
            || ($fakeId && isset($session->$fakeId))) {
            $uploadInfo = isset($session->$id) 
                ? $session->$id : $session->$fakeId;

            if ($uploadInfo) {
                $fileName = $rowAuthor->id;

                $pathInfo = pathinfo($uploadInfo['tmp_name']);
                if ($pathInfo['extension']) {
                    $fileName .= '.' . $pathInfo['extension'];
                }
                $fileName = $pathInfo['dirname'] . '/' . $fileName;

                $rowAuthor->photo = rename(
                    Pi::path($uploadInfo['tmp_name']),
                    Pi::path($fileName)
                ) ? $fileName : $uploadInfo['tmp_name'];
                $rowAuthor->save();
            }

            unset($session->$id);
            unset($session->$fakeId);
        }

        return $id;
    }

    /**
     * Default page, redirect to author list page
     * 
     * @return ViewModel 
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array('action'    => 'list'));
    }

    /**
     * Add author
     * 
     * @return ViewModel 
     */
    public function addAction()
    {
        $allowed = Service::getModuleResourcePermission('author');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $form = $this->getAuthorForm('add');
        Service::setModuleConfig($this);
        $this->view()->assign('title', __('Add author info'));
        $this->view()->setTemplate('author-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new AuthorEditFilter);
            $form->setValidationGroup(Author::getAvailableFields());

            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('There are some error occured!')
                );
            }
            
            $data = $form->getData();
            $id   = $this->saveAuthor($data);

            if (!$id) {
                return $this->renderForm(
                    $form,
                    __('Can not save data!')
                );
            }
            
            // Clear cache
            $module = $this->getModule();
            Pi::service('registry')
                ->handler('author', $module)
                ->clear($module);
            
            $this->redirect()->toRoute('', array('action' => 'list'));
        }

        $form->setData(array('fake_id'  => uniqid()));
        $this->view()->assign('form', $form);
    }
    
    /**
     * Edit author
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        $allowed = Service::getModuleResourcePermission('author');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $form = $this->getAuthorForm('edit');
        Service::setModuleConfig($this);
        $this->view()->assign('title', __('Edit Author Info'));
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new AuthorEditFilter);
            $form->setValidationGroup(Author::getAvailableFields());

            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('There are some error occured!')
                );
            }
            
            $data = $form->getData();
            $id   = $this->saveAuthor($data);
            
            // Clear cache
            $module = $this->getModule();
            Pi::service('registry')
                ->handler('author', $module)
                ->clear($module);

            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
        
        $id  = $this->params('id', 0);
        if (empty($id)) {
            return $this->jumpto404(__('Invalid author id'));
        }

        $row = $this->getModel('author')->find($id);
        if (!$row->id) {
            return $this->jumpTo404(__('The author is not exists'));
        }
        $form->setData($row->toArray());
        $form->setData(array('fake_id'  => uniqid()));
        $this->view()->assign('form', $form);
    }
    
    /**
     * Deleting authors by given id
     * 
     * @return ViewModel
     */
    public function deleteAction()
    {
        $allowed = Service::getModuleResourcePermission('author');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $id     = $this->params('id');
        $ids    = array_filter(explode(',', $id));
        if (empty($ids)) {
            return $this->jumpTo404(__('Invalid author id!'));
        }

        $modelAuthor = $this->getModel('author');
        // Clear article author
        $this->getModel('article')->update(
            array('author' => 0),
            array('author' => $ids)
        );

        // Delete photo
        $resultset = $modelAuthor->select(array('id' => $ids));
        foreach ($resultset as $row) {
            if ($row->photo) {
                unlink(Pi::path($row->photo));
            }
        }

        // Delete author
        $modelAuthor->delete(array('id' => $ids));
        
        // Clear cache
        $module = $this->getModule();
        Pi::service('registry')
            ->handler('author', $module)
            ->clear($module);

        // Go to list page
        return $this->redirect()->toRoute('', array('action' => 'list'));
    }

    /**
     * List all authors 
     */
    public function listAction()
    {
        $allowed = Service::getModuleResourcePermission('author');
        if (!$allowed) {
            return $this->jumpToDenied();
        }
        
        $page   = Service::getParam($this, 'p', 1);
        $name   = Service::getParam($this, 'name', '');
        $limit  = $this->config('author_limit') > 0
            ? $this->config('author_limit') : 20;
        $offset = $limit * ($page - 1);

        $module = $this->getModule();
        $model  = $this->getModel('author');
        $select = $model->select();
        if ($name) {
            $select->where->like('name', "%{$name}%");
        }
        $select->order('name ASC')->offset($offset)->limit($limit);

        $resultset = $model->selectWith($select);

        // Total count
        $select = $model->select()->columns(
            array('total' => new Expression('count(id)'))
        );
        if ($name) {
            $select->where->like('name', "%{$name}%");
        }
        $authorCountResultset = $model->selectWith($select);
        $totalCount = intval($authorCountResultset->current()->total);

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
                    'controller'    => 'author',
                    'action'        => 'list',
                    'name'          => $name,
                )),
            ));

        $this->view()->assign(array(
            'title'     => __('Author List'),
            'authors'   => $resultset,
            'paginator' => $paginator,
        ));
    }
    
    /**
     * Saving image by AJAX, but do not save data into database.
     * If the image is fetched by upload, try to receive image by Upload class,
     * if the image is from media, try to copy it from media to author path.
     * Finally the image data will be saved into session.
     * 
     */
    public function saveImageAction()
    {
        Pi::service('log')->active(false);
        
        $return = array('status' => false);
        
        $id     = $this->params('id', 0);
        if (empty($id)) {
            $id = $this->params('fake_id', 0);
        }
        if (empty($id)) {
            $return['message'] = __('Invalid ID!');
            echo json_encode($return);
            exit;
        }
        
        $uploadFakeId = $this->params('upload_id', 0);
        if (empty($uploadFakeId)) {
            $return['message'] = __('Invalid image fake ID!');
            echo json_encode($return);
            exit;
        }

        $module  = $this->getModule();
        $session = Service::getUploadSession($module, 'author');
        $image   = $session->$uploadFakeId;
        if (empty($image['tmp_name']) 
            or !file_exists(Pi::path($image['tmp_name']))
        ) {
            $return['message'] = __('Image is not exists!');
            echo json_encode($return);
            exit;
        }
        $sourceName = $image['tmp_name'];
        
        $ext      = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
        $fileName = dirname($sourceName) . '/' . $id . '.' . $ext;

        $width  = $this->params('w', 0);
        $height = $this->params('h', 0);
        $x      = $this->params('x', 0);
        $y      = $this->params('y', 0);
        if (empty($width) or empty($height)) {
            $return['message'] = __('Image width or height is needed');
            echo json_encode($return);
            exit;
        }

        // Crop and resize avatar
        Pi::image()->crop(
            $sourceName,
            array($x, $y),
            array($width, $height),
            $fileName
        );
        Pi::image()->resize(
            $fileName,
            array($this->config('author_size'), $this->config('author_size'))
        );
        
        // Scale image
        $uploadInfo = array();
        $uploadInfo['tmp_name'] = $fileName;
        $uploadInfo['w']        = $this->config('author_size');
        $uploadInfo['h']        = $this->config('author_size');
        
        Service::saveImage($uploadInfo);

        $rowAuthor = $this->getModel('author')->find($id);
        if ($rowAuthor) {
            if ($rowAuthor->photo && $rowAuthor->photo != $fileName) {
                @unlink(Pi::path($rowAuthor->photo));
            }

            $rowAuthor->photo = $fileName;
            $rowAuthor->save();
        } else {
            // Or save info to session
            $session = Service::getUploadSession($module, 'author');
            $session->$id = $uploadInfo;
        }

        $imageSize = getimagesize(Pi::path($fileName));
        
        @unlink(Pi::path($sourceName));

        // Prepare return data
        $return['data'] = array(
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
     * Remove image by AJAX.
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
            $rowAuthor = $this->getModel('author')->find($id);

            if ($rowAuthor && $rowAuthor->photo) {
                // Delete photo
                @unlink(Pi::path($rowAuthor->photo));

                // Update db
                $rowAuthor->photo = '';
                $affectedRows     = $rowAuthor->save();
            }
        } else if ($fakeId) {
            $session = Service::getUploadSession($module, 'author');

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
    
    /**
     * Process upload image by AJAX 
     */
    public function uploadAction()
    {
        Pi::service('log')->active(false);
        
        $module   = $this->getModule();
        $config   = Pi::service('module')->config('', $module);

        $return   = array('status' => false);
        $fakeId   = $this->params('fake_id', 0);

        // Checking whether ID is empty
        if (empty($fakeId)) {
            $return['message'] = __('Invalid fake ID!');
            echo json_encode($return);
            exit ;
        }
        
        $rawInfo  = $this->request->getFiles('upload');
        
        // Rename
        $ext      = strtolower(pathinfo($rawInfo['name'], PATHINFO_EXTENSION));
        $rename   = $fakeId . '.' . $ext;

        // Get path to store
        $destination = Service::getTargetDir('author', $module, true, false);

        $upload    = new UploadHandler;
        $upload->setDestination(Pi::path($destination))
            ->setRename($rename)
            ->setExtension($config['image_extension'])
            ->setSize(Media::transferSize($config['max_media_size'], false));
        
        // Get raw file name
        if (empty($rawInfo)) {
            $content = $this->request->getContent();
            preg_match('/filename="(.+)"/', $content, $matches);
            $rawName = $matches[1];
        } else {
            $rawName = null;
        }
        
        // Checking whether uploaded file is valid
        if (!$upload->isValid($rawName)) {
            $return['message'] = implode(', ', $upload->getMessages());
            echo json_encode($return);
            exit ;
        }

        $upload->receive();
        $fileName = $destination . '/' . $rename;
        
        // Resolve allowed image extension
        $imageSize    = array();
        $imageSizeRaw = getimagesize(Pi::path($fileName));
        $imageSize['w'] = $imageSizeRaw[0];
        $imageSize['h'] = $imageSizeRaw[1];
        
        $uploadInfo = array(
            'tmp_name'  => $fileName,
            'w'         => $imageSize['w'],
            'h'         => $imageSize['h'],
        );

        // Save info to session
        $session = Service::getUploadSession($module, 'author');
        $session->$fakeId = $uploadInfo;
        
        // Prepare return data
        $return['data'] = array_merge(
            array(
                'originalName' => $rawInfo['name'],
                'size'         => $rawInfo['size'],
                'preview_url'  => Pi::url($fileName),
                'basename'     => basename($fileName),
                'type'         => $ext,
                'id'           => $fakeId,
                'filename'     => $fileName,
            ),
            $imageSize
        );
        $return['status'] = true;
        echo json_encode($return);
        exit;
    }
    
    /**
     * Remove uploaded but not saved image by AJAX 
     */
    public function removeUploadAction()
    {
        Pi::service('log')->active(false);
        
        $module   = $this->getModule();
        $return   = array('status' => false);
        
        $fakeId = $this->params('fake_id', 0);
        
        // Checking whether ID is empty
        if (empty($fakeId)) {
            $return['message'] = __('Invalid fake ID!');
            echo json_encode($return);
            exit ;
        }
        
        // Save info to session
        $session = Service::getUploadSession($module, 'author');
        $image   = $session->$fakeId;
        
        if ($image and file_exists(Pi::path($image['tmp_name']))) {
            @unlink(Pi::path($image['tmp_name']));
            @unlink($session->$fakeId);
        }
        
        $return['status'] = true;
        echo json_encode($return);
        exit;
    }
}
