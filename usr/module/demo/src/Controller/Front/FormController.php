<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Demo\Form\DemoForm;

class FormController extends ActionController
{
    /**
     * Form demos
     */
    public function indexAction()
    {
        $form = new DemoForm;
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
        }
        $this->view()->assign('form', $form);
    }
}
