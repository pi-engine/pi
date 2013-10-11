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
use Module\User\Form\PasswordForm;
use Module\User\Form\PasswordFilter;
use Module\User\Form\FindPasswordForm;
use Module\User\Form\FindPasswordFilter;

/**
 * Password controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class PasswordController extends ActionController
{
    /**
     * Change password for current user
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid = Pi::user()->getIdentity();

        // Redirect login page if not logged in
        if (!$uid) {
            $this->jump(
                array(
                    '',
                    'controller' => 'login',
                    'action'     => 'index',
                ),
                __('Change password need login'),
                3
            );
            return;
        }

        $form = new PasswordForm('password-change');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Verify password
                $row = Pi::model('user_account')->find($uid, 'id');
                $credential = md5(sprintf(
                    '%s%s%s',
                    $row->salt,
                    $values['credential'],
                    Pi::config('salt')
                ));

                if ($credential == $row->credential) {
                    // Update password
                    Pi::api('user', 'user')->updateAccount(
                        $uid,
                        array(
                            'credential' => $values['credential-new']
                        )
                    );
                    return array(
                        'status' => 1,
                        'message' => __('Reset password successfully')
                    );
                } else {
                    return array(
                        'status' => 0,
                        'message' => __('Input password error')
                    );
                }
            } else {
                return array(
                    'status' => 0,
                    'message' => $form->getMessages(),
                );
            }
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

        $this->view()->assign(array(
            'form'      => $form,
            'groups'    => $groups,
            'cur_group' => 'password',
        ));
    }

    /**
     * 1. Display find password form
     * 2. Verify email
     * 3. Send verify email
     *
     */
    public function findAction()
    {
        $form = new FindPasswordForm('find-password');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new FindPasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $value = $form->getData();

                // Check email is  exist
                $userRow = $this->getModel('account')->find($value['email'], 'email');
                if (!$userRow) {
                    return array(
                        'status'  => 0,
                        'message' => __('Find password faild'),
                    );
                }

                // Set user data
                $uid = $userRow->id;
                $token = md5(uniqid($uid));
                $result = Pi::user()->data()->set(
                    $uid,
                    'find-password',
                    $token
                );

                // Send verify email
                $to = $userRow->email;
                $url = $this->url('', array(
                        'action' => 'process',
                        'id'     => md5($uid),
                        'token'  => $result['token']
                    )
                );
                $link = Pi::url($url, true);
                list($subject, $body, $type) = $this->setMailParams(
                    $userRow->username,
                    $link
                );
                Pi::api('user', 'mail')->send($to, $subject, $body, $type);

                return array(
                    'status'  => 1,
                    'message' => __('Send email successfully'),
                );
            } else {
                return array(
                    'status'  => 0,
                    'message' => $form->getMessages(),
                );
            }
        }

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('password-find');
    }

    /**
     * 1. Verify find password link
     * 2. Update user information
     */
    public function processAction()
    {
        $key      = $this->params('id', '');
        $token    = $this->params('token', '');

        $data = array(
            'status'  => 0,
        );

        // Assign title to template
        $this->view()->assign('title', __('Find password'));
        // Verify link invalid
        if (!$key || !$token) {
            $this->view()->assign('data', $data);
            return;
        }

        $userData = Pi::user()->data()->find(array(
            'content' => $token,
            'name'    => 'find-password',
        ));

        if ($userData) {
            $hashUid = md5($userData['uid']);
            $userRow = $this->getModel('account')->find($userData['uid'], 'id');

            if ($userRow && $hashUid == $key) {
                $expire  =  $userData['time'] + 24 * 3600;
                $current = time();

                // Valid verify link
                if ($current < $expire) {
                    // Display reset password form
                    $identity = $userRow->identity;
                    $uid      = $userRow->id;
                    $form     = new PasswordForm('find-password', 'find');
                    if ($this->request->isPost()) {
                        $data = $this->request->getPost();
                        $form->setInputFilter(new PasswordFilter('find'));
                        $form->setData($data);

                        if ($form->isValid()) {
                            $values = $form->getData();

                            // Update user account data
                            Pi::api('user', 'user')->updateAccount(
                                $uid,
                                array('credential' => $values['credential-new'])
                            );

                            // Delete find password verify token
                            Pi::user()->data()->delete($uid, 'find-password');

                            $data['status'] = 1;
                        } else {
                            $data['status'] = 1;
                            $this->view()->assign(array(
                                'form'    => $form,
                                'message' => __('Input is invalid, please try again later'),
                            ));
                        }
                    } else {
                        $form->setData(array('identity', $identity));
                        $this->view()->assign('form', $form);
                        $data['status'] = 1;
                    }
                }
            }
        }

        $this->view()->assign(array(
            'data' => $data,
        ));
    }


    /**
     * Show information about find password email result
     */
    public function displayAction()
    {
        $this->view()->assign(array(
            'title' => __('Find password'),
        ));
    }

    /**
     * Set mail params
     *
     * @param $username
     * @param $link
     * @return array
     */
    protected function setMailParams($username, $link)
    {
        $params = array(
            'username'           => $username,
            'find_password_link' => $link,
            'sn'                 => _date(),
        );

        // Load from HTML template
        $data = Pi::service('mail')->template(
            'find-password-mail-html',
            $params
        );

        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];

        return array($subject, $body, $type);
    }

    public function testAction()
    {
        $this->view()->setTemplate(false);
    }
}
