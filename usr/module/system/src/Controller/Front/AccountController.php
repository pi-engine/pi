<?php
/**
 * User account controller
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
use Module\System\Form\AccountForm;
use Module\System\Form\AccountFilter;

/**
 * Feature list:
 * 1. Personal account
 * 2. Edit account
 * 3. Entries to other actions
 */
class AccountController extends ActionController
{
    protected $columns = array(
        'name', 'identity', 'email'
    );

    public function indexAction()
    {
        $identity = Pi::service('authentication')->getIdentity();
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }
        $row = Pi::model('user')->find($identity, 'identity');
        $role = Pi::model('user_role')->find($row->id, 'user')->role;
        $roleRow = Pi::model('acl_role')->find($role, 'name');
        $user = array(
            __('ID')        => $row->id,
            __('Identity')  => $row->identity,
            __('Email')     => $row->email,
            __('Name')      => $row->name,
            __('Role')      => __($roleRow->title),
        );

        $title = __('User account');
        $this->view()->assign(array(
            'title' => $title,
            'user'  => $user,
        ));
        $this->view()->setTemplate('account');
    }

    public function editAction()
    {
        $identity = Pi::service('authentication')->getIdentity();
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }
        $row = Pi::model('user')->find($identity, 'identity');
        $form = new AccountForm('user-edit', $row);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new AccountFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->columns)) {
                        unset($values[$key]);
                    }
                }
                $identityChanged = ($row->identity !== $values['identity']) ? true : false;
                $row->assign($values);
                $row->save();
                if ($row->id) {
                    $message = __('User data saved successfully.');
                    if ($identityChanged) {
                        Pi::service('authentication')->clearIdentity();
                        //Pi::service('authentication')->wakeup($row->identity);
                    }

                    $this->redirect()->toRoute('', array('action' => 'index'));
                    return;
                } else {
                    $message = __('User data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            //$form->setAttribute('action', $this->url('', array('action' => 'edit')));
            $message = '';
        }

        $title = __('Edit account');
        $this->view()->assign(array(
            'title'     => $title,
            'form'      => $form,
            'message'   => $message,
        ));

        $this->view()->setTemplate('account-edit');
    }
}
