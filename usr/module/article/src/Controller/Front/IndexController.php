<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Pi\Mvc\Controller\ActionController;

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
            [
                'module'     => $module,
                'controller' => 'article',
                'action'     => 'index',
            ]
        );
    }
}
