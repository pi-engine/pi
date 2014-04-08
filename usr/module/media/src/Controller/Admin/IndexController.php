<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Pi\Mvc\Controller\ActionController;

/**
 * Index controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class IndexController extends ActionController
{
    /**
     * Jump to all media list page
     * 
     * @return ViewModel 
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array(
            'controller' => 'list',
            'action'     => 'index',
        ));
    }
}
