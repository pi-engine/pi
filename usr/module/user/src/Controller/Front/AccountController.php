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
        // From verify error message
        $errorMsg     = '';
        // Update status
        $updateStatus = '';

        // Check login in
        if (!Pi::service('user')->hasIdentity()) {
            $this->redirect()->toRoute(
                'default',
                array('controller' => 'login')
            );
            return;
        }

        $uid = Pi::service('user')->getIdentity();

        // Get username and email
        list($username, $email) = Pi::api('user', 'user')->get(
            $uid,
            array('identity', 'email')
        );

        $form = new AccountForm('account');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new AccountFilter());
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();
                $updateStatus = Pi::api('user', 'user')
                    ->updateUser($uid, $values);
            } else {
                $errorMsg = __('Input data invalid');
            }
        }

        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();vd($groups);
        foreach ($groups as $key => &$group) {
            $action = $group['compound'] ? 'edit.compound' : 'edit.profile';
            $group['link'] = $this->url(
                'default',
                array(
                    'controller' => 'profile',
                    'action'     => $action,
                    'group'      => $key,
                )
            );
        }

        $this->view()->setTemplate('account-index');
        $this->view()->assign(array(
            'username'     => $username,
            'email'        => $email,
            'form'         => $form,
            'errorMsg'     => $errorMsg,
            'updateStatus' => $updateStatus,
            'groups'       => $groups,
        ));
    }
}
