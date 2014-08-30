<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        //$form->get('fake_id')->setValue(uniqid());
        
        return $form;
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
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid        = Pi::user()->getId();
        $config     = $this->config();
        $filename   = Pi::user()->get($uid, 'avatar');
        $source     = Pi::service('avatar')->getType($filename);

        // Get required sizes from configuration
        $form       = $this->getAvatarForm('avatar');

        // Available size list
        $sizeList   = array();

        // Get allowed adapter
        $adapters   = (array) Pi::avatar()->getOption('adapter');
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
                    'label' => __('Max file size:'),
                    'text'  => $config['max_size'] . ' KB',
                );
            }
            if (!empty($config['max_avatar_width'])) {
                $limits[] = array(
                    'label' => __('Max image size:'),
                    'text'  => $config['max_avatar_width'] . ' x '
                            . $config['max_avatar_height'],
                );
            }

            $allSize = $uploadAdapter->getSize();
            arsort($allSize);
            $sizeList['upload'] = $allSize;
            if ('upload' == $source) {
                foreach ($allSize as $key => $value) {
                    $uploads[$key] = $uploadAdapter->getSource($uid, $value);
                }
            }

            $this->view()->assign(array(
                'limits'    => $limits,
                'uploads'   => $uploads,
            ));
        }
        
        // Get gravatar photo
        if (in_array('gravatar', $adapters)) {
            $gravatarAdapter  = Pi::avatar()->getAdapter('gravatar');
            $allSize = $gravatarAdapter->getSize();
            arsort($allSize);
            $sizeList['gravatar'] = $allSize;
            foreach ($allSize as $key => $value) {
                $gravatar[$key] = $gravatarAdapter->getSource($uid, $value);
            }
            $this->view()->assign('gravatar', $gravatar);
        }
        
        // Get select photo
        if (in_array('select', $adapters)) {
            $selectAdapter = Pi::avatar()->getAdapter('select');
            $selects = $selectAdapter->getMeta();
            $allSize = $selectAdapter->getSize();
            arsort($allSize);
            $sizeList['select'] = $allSize;
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
        $allSize = $localAdapter->getSize();
        arsort($allSize);
        $sizeList['local'] = $allSize;
        foreach ($allSize as $key => $value) {
            $local[$key] = $localAdapter->getSource($uid, $value);
        }
        
        // Get source
        $email  = 'gravatar' == $source ? $filename : Pi::user()->get($uid, 'email');

        // Get side nav items
        //$groups = Pi::api('group', 'user')->getList();
        //$user = Pi::api('user', 'user')->get($uid, array('uid', 'name'));
        
        $this->view()->assign(array(
            'title'    => __('Avatar Settings'),
            'form'     => $form,
            'config'   => $config,
            'allSize'  => $sizeList,
            'email'    => $email,
            'source'   => $source,
            'filename' => $filename,
            'adapters' => $adapters,
            'local'    => $local,
            //'groups'   => $groups,
            'uid'      => $uid,
            //'user'     => $user,
        ));

        $this->view()->headTitle(__('Change avatar'));
        $this->view()->headdescription(__('Customize your avatar anyway you like'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }
    
    /**
     * Process upload image by AJAX 
     */
    public function uploadAction()
    {
       
        $module   = $this->getModule();
        $config   = Pi::user()->config('');

        $result   = array('status' => false);
        $fakeId   = $this->params('fake_id', 0);

        // Checking whether ID is empty
        if (empty($fakeId)) {
            $result['message'] = __('Invalid token!');

            return $result;
        }
        
        $rawInfo  = $this->request->getFiles('upload');
        
        // Rename
        $ext      = strtolower(pathinfo($rawInfo['name'], PATHINFO_EXTENSION));
        $rename   = $fakeId . '.' . $ext;

        // Get path to store
        $location = $this->config('path_tmp')
            ?: sprintf('upload/%s/tmp', $this->getModule());
        //$destination = Pi::path($location);

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
        $upload->setDestination($location)
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
            $result['message'] = implode(', ', $upload->getMessages());

            return $result;
        }

        $upload->receive();
        //$fileName = $upload->getDestination() . '/' . $rename;
        $fileName = $location . '/' . $upload->getUploaded();

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
        $result['data'] = array_merge(
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
        $result['status'] = true;

        //return $result; for ie10+ bug
        Pi::service('log')->mute();
        echo json_encode($result);
        exit;
    }
    
    /**
     * Get gravatar URL by AJAX 
     */
    public function gravatarAction()
    {
        Pi::service('log')->active(false);
        
        $result  = array('status' => false);
        $email   = $this->params('email', '');
        if (empty($email)) {
            $result['message'] = __('Invalid email for gravatar.');

            return $result;
        }
        
        $adapter = Pi::avatar()->getAdapter('gravatar');
        $url     = $adapter->getUrl($email);
        
        $result = array(
            'status'        => true,
            'preview_url'   => $url,
            'message'       => __('Successful.'),
        );

        return $result;
    }
    
    /**
     * Get selected repository image details by AJAX 
     */
    public function repositoryAction()
    {
        Pi::service('log')->active(false);
        
        $result  = array('status' => false);
        
        $name    = $this->params('name', '');
        if (empty($name)) {
            $result['message'] = __('No image selected!');

            return $result;
        }
        
        $adapter  = Pi::avatar()->getAdapter('select');
        $lists    = $adapter->getMeta();
        $result   = $lists[$name];
        $basename = basename($result);
        $ext      = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        $dirname  = dirname($result);
        
        $result = array(
            'status'    => true,
            'ext'       => $ext,
            'dirname'   => $dirname,
            'message'   => __('Successful.'),
        );

        return $result;
    }
    
    /**
     * Save avatar by AJAX 
     */
    public function saveAction()
    {
        Pi::service('log')->mute();
        
        $result = array('status' => false);
        $source = $this->params('source', '');
        $adapters = (array) Pi::service('avatar')->getOption('adapter');
        array_push($adapters, 'local');
        if (empty($source) || !in_array($source, $adapters)) {
            $result['message'] = sprintf(__('Invalid source %s.'), $source);

            return $result;
        }
        
        $uid     = Pi::user()->getId();
        $adapter = Pi::avatar()->getAdapter($source);
        
        if ('upload' == $source) {
            $fakeId = $this->params('fake_id', 0);
            if (empty($fakeId)) {
                $result['message'] = __('Not image selected.');

                return $result;
            }

            $module     = $this->getModule();
            $session    = $this->getUploadSession($module, 'avatar');
            $image      = $session->$fakeId;
            if (empty($image['tmp_name'])) {
                $result['message'] = __('Image is missing.');

                return $result;
            }
            $rawImage   = Pi::path($image['tmp_name']);
            if (!file_exists($rawImage)) {
                $result['message'] = __('Image does not exist.');

                return $result;
            }

            $width  = $this->params('w', 0);
            $height = $this->params('h', 0);
            $x      = $this->params('x', 0);
            $y      = $this->params('y', 0);
            if (empty($width) or empty($height)) {
                $result['message'] = __('Image width or height is missing.');

                return $result;
            }

            // Crop and resize avatar
            $paths    = $adapter->getMeta($uid);
            foreach ($paths as $path) {
                Pi::image()->crop(
                    $rawImage,
                    array($x, $y),
                    array($width, $height),
                    $path['path']
                );
                Pi::image()->resize(
                    $path['path'],
                    array($path['size'], $path['size'])
                );
            }
            @unlink($rawImage);

            $meta = array_pop($paths);
            $photo = basename($meta['path']);

        } elseif ('gravatar' == $source) {
            $email = $this->params('email', '');
            if (empty($email)) {
                $result['message'] = __('Email address is missing.');

                return $result;
            }
            
            $photo = $email;
        } elseif ('select' == $source) {
            $name = $this->params('name', '');
            if (empty($name)) {
                $result['message'] = __('Image name is missing.');

                return $result;
            }
            
            $photo = $name;
        } else {
            $photo = '';
        }
        
        // Remove old uploaded photo
        $oldAvatar = Pi::user()->get($uid, 'avatar');
        if ($oldAvatar != $photo) {
            /*
            // Remove deprecated avatar
            $adapter   = Pi::avatar()->getAdapter('upload');
            if ($adapter) {
                $oldPaths  = $adapter->getMeta($uid, $oldAvatar);
                foreach ($oldPaths as $oldPath) {
                    $oldFile = dirname($oldPath['path']) . '/' . $oldAvatar;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            }
            */

            // Save avatar data into database
            Pi::user()->set($uid, 'avatar', $photo);
        }

        // Trigger event
        Pi::service('event')->trigger('avatar_change', $uid);

        $result['status'] = true;
        $result['message'] = __('Avatar set successfully.');

        return $result;
    }
    
    /**
     * Remove temporary image by AJAX 
     */
    public function removeAction()
    {
        Pi::service('log')->active(false);
        
        $result = array('status' => false);
        $fakeId = $this->params('fake_id', 0);
        if (empty($fakeId)) {
            $result['message'] = __('Image is not removed.');

            return $result;
        }
        
        $module  = $this->getModule();
        $session = $this->getUploadSession($module, 'avatar');
        $image   = $session->$fakeId;
        
        if (!empty($image['tmp_name'])) {
            @unlink(Pi::path($image['tmp_name']));
            unset($session->$fakeId);
        }
        
        $result['status'] = true;
        $result['message'] = __('Image removed successfully.');

        return $result;
    }
}
