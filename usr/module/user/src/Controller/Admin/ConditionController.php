<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\ConditionFilter;
use Module\User\Form\ConditionForm;
use Pi\File\Transfer\Upload;

/**
 * Conditions controller
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class ConditionController extends ActionController
{
    /**
     * Default action
     * @return array|void
     */
    public function indexAction()
    {
        // Get condition list
        $list = Pi::api('condition', 'user')->getConditionList();
        // Set view
        $this->view()->setTemplate('condition');
        $this->view()->assign('list', $list);
    }

    public function updateAction()
    {
        $message   = null;
        $condition = null;

        if (!Pi::service('module')->isActive('user')) {
            $message = __('Please install user module !');
            $url     = ['action' => 'index'];
            $this->jump($url, $message, 'error');
        }
        // Get id
        $id = $this->params('id');

        // Set form
        $form = new ConditionForm('condition');
        $form->setAttribute('enctype', 'multipart/form-data');

        if ($id) {
            $condition = $this->getModel('condition')->find($id)->toArray();
            $form->get('filename')->setAttribute('required', null);
        }

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $file = $this->request->getFiles();

            $form->setInputFilter(new ConditionFilter());

            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();

                $isValid = true;

                // upload image
                if (!empty($file['filename']['name'])) {
                    // Set upload path
                    $destinationPath = Pi::path('upload/condition');
                    // Image name
                    // Upload

                    $finalPath = $destinationPath . '/' . $file['filename']['name'];

                    if (!is_file($finalPath) || ($id && $condition['filename'] == $file['filename']['name'])) {
                        $uploader = new Upload;
                        $uploader->setDestination($destinationPath);
                        $uploader->setRename(Pi::api('condition', 'user')->rename($file['filename']['name']));
                        $uploader->setExtension('pdf');
                        $uploader->setSize($this->config('image_size'));


                        if ($uploader->isValid()) {
                            $uploader->receive();
                            // Get image name
                            $values['filename'] = $uploader->getUploaded('filename');
                        } else {
                            $messages = $uploader->getMessages();
                            $message  = $messages ? implode('; ', $messages) : __('Problem in upload file. please try again');
                            $isValid  = false;
                        }
                    } else {
                        $message = __('Filename already exists. please try again');
                        $isValid = false;
                    }
                }

                if ($isValid) {

                    var_dump(isset($values['filename']));

                    if (!isset($values['filename']) || (isset($values['filename']) && $values['filename'] == '')) {
                        unset($values['filename']);
                    }

                    // Set time
                    if (empty($values['active_at'])) {
                        $values['active_at'] = time();
                    }

                    // Save values
                    if (!empty($values['id'])) {
                        $row = $this->getModel('condition')->find($values['id']);
                    } else {
                        $row = $this->getModel('condition')->createRow();
                    }

                    $row->assign($values);
                    $row->save();

                    $message = __('Condition data saved successfully.');
                    $url     = ['action' => 'index'];
                    $this->jump($url, $message);
                }
            }
        } else {
            if ($id) {
                $form->setData($condition);
            }
        }
        // Set view
        $this->view()->setTemplate('condition-update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add condition'));
        $this->view()->assign('message', $message);
    }

    public function removeAction()
    {
        // Get id
        $id = $this->params('id');

        $return = Pi::api('condition', 'user')->removeCondition($id);

        if ($return) {
            $this->jump(['action' => 'index'], __('Condition file has been deleted successfully'));
        } else {
            $this->jump(['action' => 'index'], __('Error occured during condition file removing'));
        }
    }
}