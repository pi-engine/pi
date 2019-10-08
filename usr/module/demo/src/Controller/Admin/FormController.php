<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Admin;

use Module\Demo\Form\BootstrapForm;
use Pi\File\Transfer\Upload;
use Pi\Mvc\Controller\ActionController;

/**
 * Feature list:
 *
 */
class FormController extends ActionController
{
    public function indexAction()
    {
        $messages = [];
        $form     = new BootstrapForm('bootstrap');
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);

            $messages[] = _a('Form submitted successfully.');

            //$rename = 'demo_for_upload_%random%';
            $rename   = function ($name) {
                //$extension = pathinfo($name, PATHINFO_EXTENSION);
                $name = 'test_for_upload_' . $name;
                return $name;
            };
            $uploader = new Upload(['rename' => $rename]);
            $uploader->setExtension('jpg,png,gif,txt,zip,rar');
            //->setRename('tmp.%random%');
            //->setImageSize(array('maxWidth' => 600, 'maxHeight' => 500));
            if ($uploader->isValid()) {
                $uploader->receive();
                $file       = $uploader->getUploaded('upload_demo');
                $messages[] = sprintf(_a('File uploaded and saved as %s'), $file);
            }
        }


        $this->view()->assign([
            'form'     => $form,
            'messages' => $messages,
        ]);
    }
}
