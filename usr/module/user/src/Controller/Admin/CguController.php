<?php
/**
* Pi Engine (http://pialog.org)
*
* @link            http://code.pialog.org for the Pi Engine source repository
* @copyright       Copyright (c) Pi Engine http://pialog.org
* @license         http://pialog.org/license.txt BSD 3-Clause License
*/

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\CguFilter;
use Module\User\Form\CguForm;
use Pi\File\Transfer\Upload;

/**
* Cgu controller
*
* @author Frédéric TISSOT <contact@espritdev.fr>
*/
class CguController extends ActionController
{
    /**
     * Default action
     * @return array|void
     */
    public function indexAction()
    {
        // Get package list
        $list = Pi::api('cgu', 'user')->getCguList();
        // Set view
        $this->view()->setTemplate('cgu');
        $this->view()->assign('list', $list);
    }

    public function updateAction()
    {
        $message = null;

        if (!Pi::service('module')->isActive('user')) {
            $message = __('Please install user module !');
            $url = array('action' => 'index');
            $this->jump($url, $message, 'error');
        }
        // Get id
        $id = $this->params('id');

        // Set form
        $form = new CguForm('cgu');
        $form->setAttribute('enctype', 'multipart/form-data');

        if ($id) {
            $cgu = $this->getModel('cgu')->find($id)->toArray();
            $form->get('filename')->setAttribute('required', null);
        }

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $file = $this->request->getFiles();

            $form->setInputFilter(new CguFilter());

            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();

                $isValid = true;

                // upload image
                if (!empty($file['filename']['name'])) {
                    // Set upload path
                    $destinationPath = Pi::path('upload/cgu');
                    // Image name
                    // Upload

                    $finalPath = $destinationPath . '/' . $file['filename']['name'];

                    if(!is_file($finalPath) || ($id && $cgu['filename'] == $file['filename']['name'])){
                        $uploader = new Upload;
                        $uploader->setDestination($destinationPath);
                        $uploader->setRename($file['filename']['name']);
                        $uploader->setExtension('pdf');
                        $uploader->setSize($this->config('image_size'));

                        if ($uploader->isValid()) {
                            $uploader->receive();
                            // Get image name
                            $values['filename'] = $uploader->getUploaded('filename');
                        } else {
                            $messages = $uploader->getMessages();
                            $message = $messages ? implode('; ', $messages) : __('Problem in upload file. please try again');
                            $isValid = false;
                        }
                    } else {
                        $message = __('Filename already exists. please try again');
                        $isValid = false;
                    }
                }

                if($isValid){
                    if (isset($values['filename']) && $values['filename'] == '') {
                        unset($values['filename']);
                    }

                    // Set time
                    if (empty($values['active_at'])) {
                        $values['active_at'] = time();
                    }

                    // Save values
                    if (!empty($values['id'])) {
                        $row = $this->getModel('cgu')->find($values['id']);
                    } else {
                        $row = $this->getModel('cgu')->createRow();
                    }
                    $row->assign($values);
                    $row->save();

                    $message = __('Cgu data saved successfully.');
                    $url = array('action' => 'index');
                    $this->jump($url, $message);
                }
            }
        } else {
            if ($id) {
                $form->setData($cgu);
            }
        }
        // Set view
        $this->view()->setTemplate('cgu-update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add cgu'));
        $this->view()->assign('message', $message);
    }

    public function removeAction(){
        // Get id
        $id = $this->params('id');

        $return = Pi::api('cgu', 'user')->removeCgu($id);

        if($return){
            $this->jump(array('action' => 'index'), __('Cgu has been deleted successfully'));
        } else {
            $this->jump(array('action' => 'index'), __('Error occured during cgu removing'));
        }
    }
}