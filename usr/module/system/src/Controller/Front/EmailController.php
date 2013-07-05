<?php
/**
 * Email controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Feature list:
 * 1. Change email
 * 2. Send activation code
 */
class EmailController extends ActionController
{
    public function indexAction()
    {
        $identity = Pi::service('authentication')->getIdentity();
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }

        $title = __('Change Email');
        $this->view()->assign(array(
            'title' => $title,
        ));
        $this->view()->setTemplate('email-change');
    }

    public function sendAction()
    {
        $title = __('Send activation');
        $this->view()->assign(array(
            'title' => $title,
        ));
        $this->view()->setTemplate('email-send');
    }
}
