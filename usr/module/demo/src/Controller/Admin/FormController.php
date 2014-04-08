<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\File\Transfer\Upload;
use Module\Demo\Form\BootstrapForm;

/**
 * Feature list:
 *
 */
class FormController extends ActionController
{
    public function indexAction()
    {
        $messages = array();
        $form = new BootstrapForm('bootstrap');
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);

            $messages[] = _a('Form submitted successfully.');

            //$rename = 'demo_for_upload_%random%';
            $rename = function ($name) {
                //$extension = pathinfo($name, PATHINFO_EXTENSION);
                $name = 'test_for_upload_' . $name;
                return $name;
            };
            $uploader = new Upload(array('rename' => $rename));
            $uploader->setExtension('jpg,png,gif,txt,zip,rar');
            //->setRename('tmp.%random%');
            //->setImageSize(array('maxWidth' => 600, 'maxHeight' => 500));
            if ($uploader->isValid()) {
                $uploader->receive();
                $file = $uploader->getUploaded('upload_demo');
                $messages[] = sprintf(_a('File uploaded and saved as %s'), $file);
            }
        }


        $this->view()->assign(array(
            'form'      => $form,
            'messages'  => $messages,
        ));
    }
}
