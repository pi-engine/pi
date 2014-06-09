<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Pi;

/**
 * Index controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class IndexController extends ActionController
{
    /**
     * Default page, and it will redirect to article homepage 
     */
    public function indexAction()
    {
        $module = $this->getModule();
        return $this->redirect()->toRoute(
            'article',
            array(
                'module'        => $module,
                'controller'    => 'article',
                'action'        => 'index'
            )
        );
    }
}
