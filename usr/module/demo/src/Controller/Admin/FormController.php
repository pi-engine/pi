<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Demo\Form\BootstrapForm;

/**
 * Feature list:
 *
 *  1. List of routes
 *  2. Edit a route
 *  5. Delete a route
 */
class FormController extends ActionController
{
    public function indexAction() {
        $form = new BootstrapForm('bootstrap');

        $this->view()->assign(array(
            'form'    => $form
        ));
    }
}
