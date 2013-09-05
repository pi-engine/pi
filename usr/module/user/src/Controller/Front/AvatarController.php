<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\File\Transfer\Upload as UploadHandler;
use Module\User\Form\AvatarForm;
use Module\User\Form\AvatarFilter;

/**
 * Avatar controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AvatarController extends ActionController
{
    /**
     * Get form instance
     * 
     * @param string  $name
     * @return \Module\User\Form\AvatarForm 
     */
    protected function getAvatarForm($name)
    {
        $form   = new AvatarForm($name);
        $fakeId = uniqid();
        $form->get('fake_id')->setValue($fakeId);
        
        return $form;
    }
    
    protected function resizeAvatar($data)
    {
        
    }
    
    protected function getTargetDir(
        $section,
        $module = null,
        $autoCreate = false,
        $autoSplit = true
    ) {
        $module  = $module ?: $this->getModule();
        $config  = Pi::service('module')->config('', $module);
        $pathKey = sprintf('path_%s', strtolower($section));
        $path    = isset($config[$pathKey]) ? $config[$pathKey] : '';

        if ($autoSplit && !empty($config['sub_dir_pattern'])) {
            $path .= '/' . date($config['sub_dir_pattern']);
        }

        if ($autoCreate) {
            $this->mkdir(Pi::path($path));
        }

        return $path;
    }
    
    protected function mkdir($dir)
    {
        $result = true;

        if (!file_exists($dir)) {
            $oldumask = umask(0);

            $result   = mkdir($dir, 0777, TRUE);

            umask($oldumask);
        }

        return $result;
    }
    
    protected function getUploadSession($module = null, $type = 'default')
    {
        $module = $module ?: $this->getModule();
        $ns     = sprintf('%s_%s_upload', $module, $type);

        return Pi::service('session')->$ns;
    }
    
    public function indexAction()
    {
        $logUser = Pi::user()->id;
        if (empty($logUser)) {
            return $this->jumpToDenied();
        }
        
        $id = $this->params('id', 0);
        if ($id != $logUser) {
            return $this->jumpTo404(__('Invalid user ID!'));
        }
        
        $module = $this->getModule();
        $avatar = Pi::service('avatar')->getList((array) $id);
        $form   = $this->getAvatarForm('avatar');
        $allSize = Pi::service('avatar')->getSize();
        arsort($allSize);
        
        
        $this->view()->assign(array(
            'title'     => __('Avatar Settings'),
            'form'      => $form,
            'config'    => Pi::service('module')->config('', $module),
            'allSize'   => $allSize,
            'avatar'    => $avatar,
        ));
    }
    
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
        $destination = $this->getTargetDir('tmp', $module, true, false);

        $upload    = new UploadHandler;
        $upload->setDestination(Pi::path($destination))
                ->setRename($rename)
                ->setExtension($config['avatar_extension'])
                ->setSize($config['max_size'] * 1024 * 2024);
        
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
        $session = $this->getUploadSession($module, 'media');
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
    
    public function gavatarAction()
    {
        
    }
    
    public function repositoryAction()
    {
        
    }
    
    public function saveAction()
    {
        
    }
    
    public function removeAction()
    {
        Pi::service('log')->active(false);
        
        $return = array('status' => false);
        $fakeId = $this->params('fake_id', 0);
        if (empty($fakeId)) {
            $return['message'] = __('Can not remove photo');
            echo json_encode($return);
            exit;
        }
        
        $module  = $this->getModule();
        $session = $this->getUploadSession($module, 'media');
        $image   = $session->$fakeId;
        
        if ($image['tmp_name']) {
            @unlink(Pi::path($image['tmp_name']));
        }
        
        $return['status'] = true;
        $return['message'] = __('Remove image successful');
        echo json_encode($return);
        exit;
    }
}
