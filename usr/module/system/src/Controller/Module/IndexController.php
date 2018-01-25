<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Module;

use Pi\Mvc\Controller\ActionController;

/**
 * Module index action controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return void
     */
    public function indexAction()
    {
        $module = $this->params('module');
        $title  = __('Admin area');
        $this->view()->assign('title', $title);
        $this->view()->assign('module', $module);
        $this->view()->setTemplate('index', 'system');
    }
}
