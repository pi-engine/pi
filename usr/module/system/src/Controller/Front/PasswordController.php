<?php
/**
 * Password controller
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
use Module\System\Form\PasswordForm;
use Module\System\Form\PasswordFilter;

/**
 * Feature list:
 * 1. Change passwrod
 * 2. Find password
 */
class PasswordController extends ActionController
{
    public function indexAction()
    {
        $identity = Pi::service('authentication')->getIdentity();
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }

        $form = new PasswordForm('password-change');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $row = Pi::model('user')->find($identity, 'identity');
                $row->credential = $values['credential-new'];
                $row->prepare()->save();
                if ($row->id) {
                    $message = __('Password changed successfully.');
                    $this->redirect()->toRoute('', array('controller' => 'account', 'action' => 'index'));
                    return;
                } else {
                    $message = __('Password not changed.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $form->setData(array('identity' => $identity));
            //$form->setAttribute('action', $this->url('', array('action' => 'edit')));
            $message = '';
        }

        $title = __('Change password');
        $this->view()->assign(array(
            'title' => $title,
            'form'      => $form,
            'message'   => $message,
        ));
        $this->view()->setTemplate('password-change');
    }

    public function findAction()
    {
        $title = __('Find password');
        $this->view()->assign(array(
            'title' => $title,
        ));
        $this->view()->setTemplate('password-find');
    }
}
