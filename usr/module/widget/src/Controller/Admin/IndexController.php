<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Index Controller
 */
class IndexController extends ActionController
{
    /**
     * {@inheritDoc}
     */
    public function indexAction()
    {
        $this->redirect('', array('controller' => 'script'));
    }
}
