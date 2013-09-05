<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\AccountForm;
use Module\User\Form\AccountFilter;
use Module\User\Group;

class AccountController extends ActionController
{
    /**
     * Edit base user information
     *
     * @return array|void
     */
    public function indexAction()
    {
        $status = 0;
        $isPost = 0;
        // Check login in
        if (!Pi::service('user')->hasIdentity()) {
            $this->redirect()->toRoute(
                '',
                array('controller' => 'login')
            );
            return;
        }

        $uid = Pi::service('user')->getIdentity();

        // Get username and email
        $getData = Pi::api('user', 'user')->get(
            $uid,
            array('identity', 'email', 'name')
        );
        $username = $getData['identity'];
        $email    = $getData['email'];
        $displayName = $getData['name'];

        $form = new AccountForm('account');
        $form->setData(array('name' => $displayName));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new AccountFilter());
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();
                $updateStatus = Pi::api('user', 'user')
                    ->updateUser($uid, $values);
                $status = 1;
            }

            $isPost = 1;
        }

        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();
        foreach ($groups as $key => &$group) {
            $action = $group['compound'] ? 'edit.compound' : 'edit.profile';
            $group['link'] = $this->url(
                '',
                array(
                    'controller' => 'profile',
                    'action'     => $action,
                    'group'      => $key,
                )
            );
        }

        $user['name'] = Pi::api('user', 'user')->get($uid, 'name');
        $this->view()->setTemplate('account-index');
        $this->view()->assign(array(
            'username'     => $username,
            'email'        => $email,
            'form'         => $form,
            'groups'       => $groups,
            'curGroup'     => 'account',
            'status'       => $status,
            'isPost'       => $isPost,
            'user'         => $user,
        ));
    }
}
