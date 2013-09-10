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
use Module\System\Form\AccountForm;
use Module\System\Form\AccountFilter;

/**
 * User account controller
 *
 * Feature list:
 *
 * 1. Personal account
 * 2. Edit account
 * 3. Entries to other actions
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AccountController extends ActionController
{
    /**
     * Columns of user account model
     * @var string[]
     */
    protected $columns = array(
        'name', 'identity', 'email'
    );

    /**
     * User account data
     *
     * @return void
     */
    public function indexAction()
    {
        $identity = Pi::service('user')->getIdentity();
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }
        //$user = Pi::api('system', 'user')->getUser($identity, 'identity');
        //$role = $user->role();
        $row = Pi::model('user_account')->find($identity);
        //$role = Pi::model('user_role')->find($row->id, 'user')->role;
        $role = Pi::api('system', 'user')->getRole($row['id'], 'front');
        $roleRow = Pi::model('acl_role')->find($role, 'name');
        $user = array(
            __('ID')        => $row['id'],
            __('Identity')  => $row['identity'],
            __('Email')     => $row['email'],
            __('Name')      => $row['name'],
            __('Role')      => __($roleRow->title),
        );
        $avatar = Pi::user()->avatar($row['id']);

        $title = __('User account');
        $this->view()->assign(array(
            'title'     => $title,
            'user'      => $user,
            'avatar'    => $avatar,
        ));
        $this->view()->setTemplate('account');
    }

    /**
     * Edit user account
     *
     * @return void
     */
    public function editAction()
    {
        $identity = Pi::service('user')->getIdentity();
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }
        $row = Pi::model('user_account')->find($identity);
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
                $identityChanged = ($row->identity !== $values['identity'])
                    ? true : false;
                $row->assign($values);
                $row->save();
                if ($row->id) {
                    $message = __('User data saved successfully.');
                    if ($identityChanged) {
                        Pi::service('authentication')->clearIdentity();
                    }

                    $this->redirect()->toRoute('', array('action' => 'index'), $message);
                    return;
                } else {
                    $message = __('User data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
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
