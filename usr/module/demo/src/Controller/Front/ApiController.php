<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Controller\Front;

use Pi\Mvc\Controller\ActionController;

class ApiController extends ActionController
{
    public function indexAction()
    {
        $this->view()->setTemplate(false);

        return sprintf('Called from %s', __METHOD__);
    }

    public function testAction()
    {
        $this->view()->assign('title', __('Test for Demo API'));
        $this->view()->assign('params', $this->params()->fromRoute());
    }
}
