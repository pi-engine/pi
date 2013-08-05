<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Email operations
 *
 * Feature list:
 *
 * 1. Change email
 * 2. Send activation code
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class EmailController extends ActionController
{
    /**
     * Change email
     *
     * @return void
     */
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

    /**
     * Send activation email
     */
    public function sendAction()
    {
        $title = __('Send activation');
        $this->view()->assign(array(
            'title' => $title,
        ));
        $this->view()->setTemplate('email-send');
    }
}
