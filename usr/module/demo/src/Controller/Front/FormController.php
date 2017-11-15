<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
