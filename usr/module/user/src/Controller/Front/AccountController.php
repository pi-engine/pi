<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\AccountForm;
use Module\User\Form\AccountFilter;

/**
 * Account controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();

        // Get identity, email, name
        $data = Pi::api('user', 'user')->get(
            $uid,
            array('identity', 'email', 'name')
        );

        // Generate form
        $form           = new AccountForm('account');
        $data['uid']    = $uid;
        $data['id']     = $uid;
        $form->setData($data);
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new AccountFilter);
            $form->setData($post);

            $result = array(
                'email_value'       => $data['email'],
                'email_error'       => 0,
                'email_message'    => ' ',
                'name_value'        => $data['name'],
                'name_error'        => 0,
                'name_message'      => ' ',
            );
            if ($form->isValid()) {
                $values = $form->getData();
                // Reset email
                if ($values['email'] != $data['email']) {
                    if ($this->config('email_confirm')) {
                        $status = $this->sendConfirmationMail(
                            $uid,
                            $data['identity'],
                            $data['email'],
                            $values['email']
                        );
                        if ($status) {
                            $result['email_message']    = __('A confirmation email has been sent to you. Please check your email and confirm.');
                        } else {
                            $result['email_error']      = 1;
                            $result['email_message']    = __('It was failed to send you confirmation email. Please try later.');
                        }
                    } else {
                        $status = Pi::api('user', 'user')->updateUser(
                            $uid,
                            array(
                                'email'         => $values['email'],
                                'last_modified' => time(),
                            )
                        );
                        if ($status) {
                            $result['email_value']      = $values['email'];
                            $result['email_message']    = __('Email has been changed successfully.');
                        } else {
                            $result['email_error']      = 1;
                            $result['email_message']    = __('It was failed to save new email. Please try later.');
                        }
                    }
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
                    if ($status) {
                        $result['name_value']      = $values['name'];
                        $result['name_message']    = __('Name has been changed successfully.');
                    } else {
                        $result['name_error']      = 1;
                        $result['name_message']    = __('It was failed to save new name. Please try later.');
                    }

                    $args = array(
                        'uid'       => $uid,
                        'new_name'  => $values['name'],
                        'old_name'  => $data['name'],
                    );

                    Pi::service('event')->trigger('name_change', $args);
                }

                return $result;

            } else {
                $result['message'] = $form->getMessages();
                return $result;
            }
        }

        $this->view()->assign(array(
            'form'      => $form,
        ));

        $this->view()->headTitle(__('Account settings'));
        $this->view()->headdescription(__('Basic settings'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Reset email action
     *
     * @return array
     */
    public function resetEmailAction()
    {
        $this->view()->setTemplate('account-reset-email');

        $result = array(
            'status'  => 0,
            'message' => __('Invalid data provided for email change.'),
        );
        $token   = _get('token');
        //$email   = _get('email');

        $view = $this->view();
        $fallback = function () use ($view, $result) {
            $view->assign('result', $result);
        };

        // Check link
        if (!$token) {
            return $fallback();
        }

        // Get user data
        $userData = Pi::user()->data()->find(array(
            'name'      => 'change-email',
            'value'     => $token,
        ));
        if (!$userData) {
            return $fallback();
        }

        // Get user email data
        $email = Pi::user()->data()->get($userData['uid'], 'email-' . $token);
        if (!$email) {
            return $fallback();
        }

        // Check uid
        $userRow = $this->getModel('account')->find($userData['uid'], 'id');
        if (!$userRow) {
            return $fallback();
        }

        // Reset email
        $oldEmail = $userRow->email;
        Pi::api('user', 'user')->updateUser(
            $userData['uid'],
            array(
                'email'         => $email,
                'last_modified' => time(),
            )
        );
        Pi::user()->data()->delete($userData['uid'], 'change-email');
        Pi::user()->data()->delete($userData['uid'], 'email-' . $token);
        $args = array(
            'uid'       => $userData['uid'],
            'old_email' => $oldEmail,
            'new_email' => $email,
        );

        $this->sendSuccessMail(
            $userRow['identity'],
            $oldEmail,
            $email
        );

        // Set log
        Pi::service('event')->trigger('email_change', $args);
        $result['status'] = 1;
        $result['message'] = __('Email changed successfully.');

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
            'message' => __('Incorrect password.'),
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
        if ($user['credential'] == $user->transformCredential($credential)) {
            $result['message'] = __('Password verified.');
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
        $result = array(
            'status' => 1,
        );

        $query = array();
        foreach (array('email', 'name', 'identity') as $param) {
            $val = $this->params($param);
            if ($val) {
                $query[$param] = $val;
            }
        }
        if (!$query) {
            return $result;
        }

        $where = Pi::db()->where();
        foreach ($query as $key => $val) {
            $where->equalTo($key, $val)->or;
        }

        $count = Pi::model('user_account')->count($where);
        $result = array(
            'status'    => $count ? 1 : 0,
        );

        return $result;
    }

    /**
     * Send confirmation for email change request
     *
     * @param int $uid
     * @param string $username
     * @param string $curEmail
     * @param string $newEmail
     *
     * @return int
     */
    protected function sendConfirmationMail($uid, $username, $curEmail, $newEmail)
    {
        $result = 0;

        if (!$uid || !$newEmail) {
            return $result;
        }

        // Set user data
        $token    = $this->createToken($uid, $newEmail);
        $userData = Pi::user()->data()->set(
            $uid,
            'change-email',
            $token,
            'user',
            $this->config('email_expiration') * 3600
        );
        if (!$userData) {
            return $result;
        }
        $userData = Pi::user()->data()->set(
            $uid,
            'email-' . $token,
            $newEmail,
            'user',
            $this->config('email_expiration') * 3600
        );
        if (!$userData) {
            return $result;
        }

        // Send verify email
        $url = $this->url('', array(
            'action'    => 'reset.email',
            'token'     => $token,
            'email'     => $newEmail,
        ));
        $link = Pi::url($url, true);

        $params = array(
            'username'          => $username,
            'change_email_url'  => $link,
            'new_email'         => $newEmail,
            'old_email'         => $curEmail,
            'expiration'        => $this->config('email_expiration'),
        );
        // Load from HTML template
        $data   = Pi::service('mail')->template('reset-email-html', $params);

        // Set subject and body
        $subject = $data['subject'];
        $body    = $data['body'];
        $type    = $data['format'];

        // Sending
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($newEmail);
        $transport = Pi::service('mail')->transport();
        $transport->send($message);
        $result = 1;

        // Mail body logging
        Pi::user()->data()->set(
            $uid,
            'change-email-body',
            $data['body'],
            'user',
            $this->config('email_expiration') * 3600
        );

        return $result;

    }

    /**
     * Send notification of email change success
     *
     * @param string $username
     * @param string $oldEmail
     * @param string $newEmail
     *
     * @return void
     */
    protected function sendSuccessMail($username, $oldEmail, $newEmail)
    {
        // Set mail params
        $params = array(
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
            'username'  => $username,
        );
        // Load from HTML template
        $data = Pi::service('mail')->template('reset-email-confirm-html', $params);

        // Set subject and body
        $subject = $data['subject'];
        $body    = $data['body'];
        $type    = $data['format'];
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($oldEmail);
        $transport = Pi::service('mail')->transport();
        $transport->send($message);
    }

    /**
     * Creates token
     *
     * @param int $uid
     * @param string $email
     *
     * @return string
     */
    protected function createToken($uid, $email)
    {
        $token = md5($uid . $email . Pi::config('salt') . mt_rand());

        return $token;
    }
}
