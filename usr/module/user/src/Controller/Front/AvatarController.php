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
        $form    = new AvatarForm($name);
        $form->get('fake_id')->setValue(uniqid());
        
        return $form;
    }
    
    /**
     * Get directory to save image
     * 
     * @param string  $section
     * @param string  $module
     * @param bool    $autoCreate
     * @param bool    $autoSplit
     * @return string 
     */
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
    
    /**
     * Create a directory if it not exists
     * 
     * @param string  $dir
     * @return bool 
     */
    protected function mkdir($dir)
    {
        /*
        $result = true;

        if (!file_exists($dir)) {
            $oldumask = umask(0);

            $result   = mkdir($dir, 0777, TRUE);

            umask($oldumask);
        }
        */
        $result = Pi::service('file')->mkdir($dir);
        return $result;
    }
    
    /**
     * Get session instance
     * 
     * @param string  $module
     * @param string  $type
     * @return Pi\Application\Service\Session 
     */
    protected function getUploadSession($module = null, $type = 'default')
    {
        $module = $module ?: $this->getModule();
        $ns     = sprintf('%s_%s_upload', $module, $type);

        return Pi::service('session')->$ns;
    }
    
    /**
     * Default page
     * 
     * @return ViewModel 
     */
    public function indexAction()
    {
        $uid = Pi::user()->id;
        if (empty($uid)) {
            return $this->jumpToDenied();
        }

        /*
        $id = $this->params('id', 0);
        if ($id != $logUser) {
            return $this->jumpTo404(__('Invalid user ID!'));
        }
        */

        //$uid      = Pi::user()->id;
        $filename = Pi::user()->get($uid, 'avatar');
        
        // Get required sizes from configuration
        $form    = $this->getAvatarForm('avatar');
        $allSize = Pi::service('avatar')->getSize();
        arsort($allSize);
        
        // Get allowed adapter
        $adapters = Pi::avatar()->getOption('adapter_allowed');
        //$adapters = $options['adapter_allowed'];
        
        // Get upload photo
        if (in_array('upload', $adapters)) {
            $uploadAdapter = Pi::avatar()->getAdapter('upload');
            $paths    = $uploadAdapter->getMeta($uid);
            $uploads  = array();
            foreach ($paths as $path) {
                $type = basename(dirname($path['path']));
                $uploads[$type] = dirname($path['src']) . '/' . $filename;
            }
            $this->view()->assign('uploads', array());
        }
        
        // Get gravatar photo
        if (in_array('gravatar', $adapters)) {
            $gravatarAdapter  = Pi::avatar()->getAdapter('gravatar');
            $gravatar = $gravatarAdapter->getSource($uid);
            $this->view()->assign('gravatar', $gravatar);
        }
        
        // Get select photo
        if (in_array('select', $adapters)) {
            $selectAdapter = Pi::avatar()->getAdapter('select');
            $selects = $selectAdapter->getMeta();
            //unset($selects['blank']);
            
            // Get current selected repository photo
            $selected = array();
            foreach (array_keys($allSize) as $name) {
                $metas = $selectAdapter->getMeta($name);
                //unset($metas['blank']);
                if (in_array($filename, array_keys($selects))) {
                    $selected[$name] = $metas[$filename];
                } else {
                    $selected[$name] = array_shift($metas);
                }
            }
            
            $this->view()->assign(array(
                'selects'   => $selects,
                'selected'  => $selected,
            ));
        }
        
        // Get local photo
        $localAdapter = Pi::avatar()->getAdapter('local');
        $local        = $localAdapter->getSource($uid);
        
        // Get source
        $source = '';
        $email  = '';
        //if (preg_match('/.+@.+/', $filename)) {
        if (false !== strpos($filename, '@')) {
            $source = 'gravatar';
            $email  = $filename;
        } elseif (in_array($filename, array_keys($selects))) {
            $source = 'repository';
        } elseif ('local' == $filename) {
            $source = 'local';
        } else {
            $source = 'upload';
            $this->view()->assign('uploads', $uploads);
        }
        
        $this->view()->assign(array(
            'title'    => __('Avatar Settings'),
            'form'     => $form,
            'config'   => Pi::service('module')->config('', $this->getModule()),
            'allSize'  => $allSize,
            'email'    => $email ?: Pi::user()->get($uid, 'email'),
            'source'   => $source,
            'filename' => $filename,
            'adapters' => $adapters,
            'local'    => $local,
        ));
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
        $rename   = $fakeId . $ext;

        // Get path to store
        $destination = $this->getTargetDir('tmp', $module, true, false);

        $upload    = new UploadHandler;
        $upload->setDestination(Pi::path($destination))
            ->setRename($rename)
            ->setExtension($config['avatar_extension'])
            ->setSize($config['max_size'] * 1024 * 2024)
            ->setImageSize(array(
                'maxWidth'   => $config['max_avatar_width'],
                'maxHeight'  => $config['max_avatar_height'],
            ));
        
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
        $session = $this->getUploadSession($module, 'avatar');
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
     * Get gravatar URL by AJAX 
     */
    public function gravatarAction()
    {
        Pi::service('log')->active(false);
        
        $return  = array('status' => false);
        $email   = $this->params('email', '');
        if (empty($email)) {
            $return['message'] = __('Invalid email!');
            echo json_encode($return);
            exit ;
        }
        
        $adapter = Pi::avatar()->getAdapter('gravatar');
        $url     = $adapter->getUrl($email);
        
        $return = array(
            'status'        => true,
            'preview_url'   => $url,
            'message'       => __('Successful'),
        );
        echo json_encode($return);
        exit;
    }
    
    /**
     * Get selected repository image details by AJAX 
     */
    public function repositoryAction()
    {
        Pi::service('log')->active(false);
        
        $return  = array('status' => false);
        
        $name    = $this->params('name', '');
        if (empty($name)) {
            $return['message'] = __('No image selected!');
            echo json_encode($return);
            exit ;
        }
        
        $adapter  = Pi::avatar()->getAdapter('select');
        $lists    = $adapter->getMeta();
        $result   = $lists[$name];
        $basename = basename($result);
        $ext      = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        $dirname  = dirname($result);
        
        $return = array(
            'status'    => true,
            'ext'       => $ext,
            'dirname'   => $dirname,
            'message'   => __('Successful'),
        );
        echo json_encode($return);
        exit;
    }
    
    /**
     * Save avatar by AJAX 
     */
    public function saveAction()
    {
        Pi::service('log')->active(false);
        
        $return = array('status' => false);
        $source = $this->params('source', '');
        $adapters = array('upload', 'gravatar', 'repository', 'local');
        if (empty($source) 
            or !in_array($source, $adapters)
        ) {
            $return['message'] = __('Invalid source!');
            echo json_encode($return);
            exit;
        }
        
        $uid     = Pi::user()->id;
        $adapter = Pi::avatar()->getAdapter($source);
        
        if ('upload' == $source) {
            $fakeId = $this->params('fake_id', 0);
            if (empty($fakeId)) {
                $return['message'] = __('Not image select!');
                echo json_encode($return);
                exit;
            }

            $module  = $this->getModule();
            $session = $this->getUploadSession($module, 'avatar');
            $image   = $session->$fakeId;
            if (empty($image['tmp_name']) 
                or !file_exists(Pi::path($image['tmp_name']))
            ) {
                $return['message'] = __('Image not exists!');
                echo json_encode($return);
                exit;
            }

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
            $paths    = $adapter->getMeta($uid);
            foreach ($paths as $path) {
                Pi::image()->crop(
                    $image['tmp_name'],
                    array($x, $y),
                    array($width, $height),
                    $path['path']
                );
                Pi::image()->resize(
                    $path['path'],
                    array($path['size'], $path['size'])
                );
            }

            $photo = basename($path['path']);

            @unlink(Pi::path($image['tmp_name']));
        } elseif ('gravatar' == $source) {
            $email = $this->params('email', '');
            if (empty($email)) {
                $return['message'] = __('Invalid email!');
                echo json_encode($return);
                exit;
            }
            
            $photo = $email;
        } elseif ('repository' == $source) {
            $name = $this->params('name', '');
            if (empty($name)) {
                $return['message'] = __('Invalid repository image name!');
                echo json_encode($return);
                exit;
            }
            
            $photo = $name;
        } else {
            $photo = 'local';
        }
        
        // Remove old photo
        $oldAvatar = Pi::user()->get($uid, 'avatar');
        $adapter   = Pi::avatar()->getAdapter('upload');
        $oldPaths  = $adapter->getMeta($uid, $oldAvatar);
        foreach ($oldPaths as $oldPath) {
            $oldFile = dirname($oldPath['path']) . '/' . $oldAvatar;
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }
        
        // Save avatar data into database
        Pi::user()->set($uid, 'avatar', $photo);
        
        $return['status'] = true;
        $return['message'] = __('Save photo successful');
        echo json_encode($return);
        exit;
    }
    
    /**
     * Remove temporary image by AJAX 
     */
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
        $session = $this->getUploadSession($module, 'avatar');
        $image   = $session->$fakeId;
        
        if ($image['tmp_name']) {
            @unlink(Pi::path($image['tmp_name']));
            unset($session->$fakeId);
        }
        
        $return['status'] = true;
        $return['message'] = __('Remove image successful');
        echo json_encode($return);
        exit;
    }
}
