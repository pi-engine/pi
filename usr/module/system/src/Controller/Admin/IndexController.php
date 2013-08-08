<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Placeholder for not defined controllers
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
        $this->redirect()->toRoute(
            '',
            array('controller' => 'dashboard', 'action' => 'system')
        );

        return;
    }
}
