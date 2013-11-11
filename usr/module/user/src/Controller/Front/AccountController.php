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

/**
 * Account controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class AccountController extends ActionController
{
    /**
     * Edit base user information
     *
     * @return array|void
     */
    public function indexAction()
    {
        // Check login in
        $uid = Pi::service('user')->getId();
        if (!$uid) {
            $this->redirect()->toRoute(
                '',
                array('controller' => 'login')
            );
            return;
        }

        // Get identity, email, name
        $data = Pi::api('user', 'user')->get(
            $uid,
            array('identity', 'email', 'name')
        );

        // Get side nav items
        $groups = Pi::api('user', 'group')->getList();

        // Generate form
        $form = new AccountForm('account');
        $data['uid'] = $uid;
        $form->setData($data);
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new AccountFilter);
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();
                // Reset email
                if ($values['email'] != $data['email']) {
                    $status = $this->sendVerifyMail(
                        $uid,
                        $data['identity'],
                        $values['email']
                    );
                    if (!$status) {
                        $result['email_error'] = 1;
                    }

                    $result['email_error']   = 0;
                    $result['new_email']     = $values['email'];
                }
                // Reset display name
                if ($values['name'] != $data['name']) {
                    $status = Pi::api('user', 'user')->updateUser(
                        $uid,
                        array(
                            'name' => $values['name'],
                            'last_modified' => time(),
                        )
                    );
                    if (!$status) {
                        $result['name_error'] = 1;
                    }
                    $result['name_error'] = 0;
                }

                return $result;

            } else {
                $result['message'] = $form->getMessages();
                return $result;
            }
        }

        $user['name']     = $data['name'];
        $user['identity'] = $data['identity'];
        $user['id']      = $uid;

        $this->view()->assign(array(
            'form'      => $form,
            'groups'    => $groups,
            'cur_group' => 'account',
            'user'      => $user
        ));
    }

    /**
     * Reset email action
     *
     * @return array
     */
    public function resetEmailAction()
    {
        $result = array(
            'status'  => 0,
            'message' => __('Verify link invalid'),
        );
        $hashUid = _get('uid');
        $token   = _get('token');
        $email   = _get('email');

        $this->view()->setTemplate('account-reset-email');
        // Check link
        if (!$hashUid || !$token) {
            $this->view()->assign('result', $result);
            return;
        }

        // Get user data
        $userData = Pi::user()->data()->find(array(
            'value'     => $token,
            'name'      => 'change-email',
        ));
        // Check user data
        if (!$userData) {
            $this->view()->assign('result', $result);
            return;
        }

        // Check new email
        $email = urldecode($email);
        if ($userData['value'] != md5($userData['uid'] . $email)) {
            $this->view()->assign('result', $result);
            return;
        }

        // Check token
        if ($userData['value'] != $token) {
            $this->view()->assign('result', $result);
            return;
        }

        // Check uid
        $userRow = $this->getModel('account')->find($userData['uid'], 'id');
        if (!$userRow) {
            $this->view()->assign('result', $result);
            return;
        }
        if ($hashUid != md5($userData['uid'])) {
            $this->view()->assign('result', $result);
            return;
        }

        // Check link expire time
        $expire  = $userData['time'] + 24 * 3600;
        $current = time();
        if ($current > $expire) {
            $this->view()->assign('result', $result);
            return;
        }

        // Reset email
        Pi::api('user', 'user')->updateUser(
            $userData['uid'],
            array(
                'email'         => urldecode($email),
                'last_modified' => time(),
            )
        );
        Pi::user()->data()->delete($userData['uid'], 'change-email');
        $result['status'] = 1;
        $result['message'] = __('Reset email successfully');

        $this->view()->assign('result', $result);
    }

    /**
     * Verify credential for ajax
     *
     * @return array
     */
    public function verifyCredentialAction()
    {
        $result = array(
            'status' => 0,
            'message' => __('Incorrect password'),
        );
        $uid        = Pi::service('user')->getId();
        $credential = _get('credential');

        // Check params
        if (!$uid || !$credential) {
            return $result;
        }

        $user = Pi::model('user_account')->find($uid, 'id');
        if (!$user) {
            return $result;
        }
        // Verify
        $credential = md5(sprintf(
            '%s%s%s',
            $user['salt'],
            $credential,
            Pi::config('salt')
        ));
        if ($credential == $user['credential']) {
            $result['message'] = __('Correct password');
            $result['status']  = 1;
        }

        return $result;

    }

    /**
     * Check if email or display name exists
     *
     * @return int
     */
    public function checkExistAction()
    {
        $name  = _get('name');
        $email = _get('email');

        $row = '';
        if ($name) {
            $row = Pi::model('user_account')->find($name, 'name');
        } else {
            $row = Pi::model('user_account')->find($email, 'email');
        }

        $status = $row ? 1 : 0;

        return array(
            'status' => $status
        );
    }

    /**
     * Send verify mail
     *
     * @param $uid
     * @param $username
     * @param $email
     * @return int
     */
    protected function sendVerifyMail($uid, $username, $email)
    {
        $result = 0;

        if (!$uid || !$email) {
            return $result;
        }

        // Set user data
        $token    = md5($uid . $email);
        $userData = Pi::user()->data()->set(
            $uid,
            'change-email',
            $token
        );
        if (!$userData) {
            return $result;
        }

        // Send verify email
        $to  = $email;
        $url = $this->url('', array(
                'action'=> 'reset.email',
                'id'    => md5($uid),
                'token' => $token,
                'email' => urlencode($email),
            )
        );
        $link = Pi::url($url, true);
        list($subject, $body, $type) = $this->setMailParams(
            $username,
            $link
        );

        // Sending
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($to);
        $transport = Pi::service('mail')->transport();
        $transport->send($message);
        $result = 1;

        return $result;

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
            'username'          => $username,
            'change_email_link' => $link,
            'sn'                => _date(),
        );

        // Load from HTML template
        $data = Pi::service('mail')->template('reset-email-html', $params);
        // Set subject and body
        $subject = $data['subject'];
        $body    = $data['body'];
        $type    = $data['format'];

        return array($subject, $body, $type);

    }
}
