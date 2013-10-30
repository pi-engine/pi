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
            Pi::service('file')->mkdir(Pi::path($path));
        }

        return $path;
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
        $uid = Pi::user()->getId();
        if (empty($uid)) {
            return $this->jumpToDenied();
        }

        $config = $this->config();
        $filename = Pi::user()->get($uid, 'avatar');
        $source = Pi::service('avatar')->getType($filename);

        //d($filename);

        // Get required sizes from configuration
        $form    = $this->getAvatarForm('avatar');
        $allSize = Pi::service('avatar')->getSize();
        arsort($allSize);

        // Get allowed adapter
        $adapters = (array) Pi::avatar()->getOption('adapter');
        $adapters[] = 'local';

        // Get upload photo
        $uploads = array();
        if (in_array('upload', $adapters)) {
            $uploadAdapter  = Pi::avatar()->getAdapter('upload');
            $options = $uploadAdapter->getOptions();
            $config['upload_extension'] = implode(',', $options['extension']);
            $limits = array(array(
                    'label' => __('Allowed extensions:'),
                    'text'  => implode(' ', $options['extension']),
            ));
            if (!empty($config['max_size'])) {
                $limits[] = array(
                    'label' => 'Max files ize:',
                    'text'  => $config['max_size'] . ' KB',
                );
            }
            if (!empty($config['max_avatar_width'])) {
                $limits[] = array(
                    'label' => 'Max image size:',
                    'text'  => $config['max_avatar_width'] . ' x '
                            . $config['max_avatar_height'],
                );
            }

            if ('upload' == $source) {
                foreach ($allSize as $key => $value) {
                    $uploads[$key] = $uploadAdapter->getSource($uid, $value);
                }
            }

            //d($uploads);

            $this->view()->assign(array(
                'limits'    => $limits,
                'uploads'   => $uploads,
            ));
        }
        
        // Get gravatar photo
        if (in_array('gravatar', $adapters)) {
            $gravatarAdapter  = Pi::avatar()->getAdapter('gravatar');

            foreach ($allSize as $key => $value) {
                $gravatar[$key] = $gravatarAdapter->getSource($uid, $value);
            }
            $this->view()->assign('gravatar', $gravatar);
        }
        
        // Get select photo
        if (in_array('select', $adapters)) {
            $selectAdapter = Pi::avatar()->getAdapter('select');
            $selects = $selectAdapter->getMeta();
            
            // Get current selected repository photo
            $selected = array();
            foreach (array_keys($allSize) as $name) {
                $metas = $selectAdapter->getMeta($name);
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
        foreach ($allSize as $key => $value) {
            $local[$key] = $localAdapter->getSource($uid, $value);
        }
        
        // Get source

        $email  = 'gravatar' == $source ? $filename : Pi::user()->get($uid, 'email');
        /*
        if (false !== strpos($filename, '@')) {
            $source = 'gravatar';
            $email  = $filename;
        } elseif (isset($selects[$filename])) {
            $source = 'repository';
        } elseif ('local' == $filename) {
            $source = 'local';
        } else {
            $source = 'upload';
            //$this->view()->assign('uploads', $uploads);
        }
        */

        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();
        $user = Pi::api('user', 'user')->get($uid, array('uid', 'name'));
        
        $this->view()->assign(array(
            'title'    => __('Avatar Settings'),
            'form'     => $form,
            'config'   => $config,
            'allSize'  => $allSize,
            'email'    => $email,
            'source'   => $source,
            'filename' => $filename,
            'adapters' => $adapters,
            'local'    => $local,
            'groups'   => $groups,
            'uid'      => $uid,
            'user'     => $user,
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

        $uploadConfig   = Pi::service('avatar')->getOption('upload');
        $extension      = implode(',', $uploadConfig['extension']);
        $maxFile        = $config['max_size'] ? $config['max_size'] * 1024 : 0;
        $maxSize        = array();
        if ($config['max_avatar_width']) {
            $maxSize['width'] = (int) $config['max_avatar_width'];
        }
        if ($config['max_avatar_height']) {
            $maxSize['height'] = (int) $config['max_avatar_height'];
        }
        $upload = new UploadHandler;
        $upload->setDestination(Pi::path($destination))
            ->setRename($rename)
            ->setExtension($extension);
        if ($maxFile) {
            $upload->setSize($maxFile);
        }
        if ($maxSize) {
            $upload->setImageSize($maxSize);
        }

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
            $return['message'] = __('Invalid email.');
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
        $adapters = (array) Pi::service('avatar')->getOption('adapter');
        array_push($adapters, 'local');
        //array('upload', 'gravatar', 'repository', 'local');
        //var_dump($source);
        //var_dump($adapters);
        if (empty($source) 
            || !in_array($source, $adapters)
        ) {
            $return['message'] = sprintf(__('Invalid source %s.'), $source);
            echo json_encode($return);
            exit;
        }
        
        $uid     = Pi::user()->getId();
        $adapter = Pi::avatar()->getAdapter($source);
        
        if ('upload' == $source) {
            $fakeId = $this->params('fake_id', 0);
            if (empty($fakeId)) {
                $return['message'] = __('Not image selected.');
                echo json_encode($return);
                exit;
            }

            $module  = $this->getModule();
            $session = $this->getUploadSession($module, 'avatar');
            $image   = $session->$fakeId;
            if (empty($image['tmp_name']) 
                || !file_exists(Pi::path($image['tmp_name']))
            ) {
                $return['message'] = __('Image does not exist.');
                echo json_encode($return);
                exit;
            }

            $width  = $this->params('w', 0);
            $height = $this->params('h', 0);
            $x      = $this->params('x', 0);
            $y      = $this->params('y', 0);
            if (empty($width) or empty($height)) {
                $return['message'] = __('Image width or height is needed.');
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
                $return['message'] = __('Invalid email.');
                echo json_encode($return);
                exit;
            }
            
            $photo = $email;
        } elseif ('select' == $source) {
            $name = $this->params('name', '');
            if (empty($name)) {
                $return['message'] = __('Invalid image file.');
                echo json_encode($return);
                exit;
            }
            
            $photo = $name;
        } else {
            $photo = '';
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
        $return['message'] = __('Avatar set successfully.');
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
            $return['message'] = __('Image is not removed.');
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
        $return['message'] = __('Image removed successfully.');
        echo json_encode($return);
        exit;
    }
}
