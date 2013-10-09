<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Module\Article\Service;
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
        return $this->redirect()->toRoute(
            Service::getRouteName(), 
            array(
                'controller' => 'article', 
                'action'     => 'index'
            )
        );
    }
}
