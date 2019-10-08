<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Admin;

use Pi\Mvc\Controller\ActionController;

/**
 * Index controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class IndexController extends ActionController
{
    /**
     * Default page, redirect to all article list page
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', [
            'controller' => 'article',
            'action'     => 'published',
            'from'       => 'all',
        ]);
    }
}
