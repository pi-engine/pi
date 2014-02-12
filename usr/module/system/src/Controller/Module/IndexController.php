<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Module;

use Pi;
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
        $title = __('Admin area');
        $this->view()->assign('title', $title);
        $this->view()->assign('module', $module);
        $this->view()->setTemplate('index', 'system');
    }
}
