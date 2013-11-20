<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Uclient\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Uclient\Form\ResetPasswordForm;
use Module\Uclient\Form\ResetPasswordFilter;
use Module\Uclient\Form\FindPasswordForm;
use Module\Uclient\Form\FindPasswordFilter;

/**
 * Password controller
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class PasswordController extends ActionController
{
    public function indexAction()
    {
        $this->view()->setTemplate(false);
    }

    /**
     * 1. Display find password form
     * 2. Verify email
     * 3. Send verify email
     */
    public function findAction()
    {
        $result = array(
            'status'  => 0,
            'message' => __('Find password failed'),
        );
        $form = new FindPasswordForm('find-password');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new FindPasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $value = $form->getData();
                $userRow = Pi::service('user')->getUser($value['email'], 'email');
                if (!$userRow) {
                    $this->view()->assign(array(
                        'form'   => $form,
                        'result' => $result,
                    ));

                    return;
                }

                // Set user data
                $uid = (int) $userRow['id'];
                $token = md5(mt_rand() . $uid);
                Pi::user()->data()->set(
                    $uid,
                    'find-password',
                    $token
                );

                // Send verify email
                $to = $userRow['email'];
                $url = $this->url('', array(
                        'action'    => 'process',
                        'uid'       => md5($uid),
                        'token'     => $token
                    )
                );
                $link = Pi::url($url, true);
                list($subject, $body, $type) = $this->setMailParams(
                    $userRow['identity'],
                    $link
                );
                $message = Pi::service('mail')->message($subject, $body, $type);
                $message->addTo($to);
                $transport = Pi::service('mail')->transport();
                $transport->send($message);

                $result['status']  = 1;
                $result['message'] = __(
                    'Send email successfully. check email and reset password.'
                );
            }

            $this->view()->assign('result', $result);
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
        $result = array(
            'status'  => 0,
            'message' => '',
        );
        $hashUid  = _get('uid');
        $token    = _get('token');

        // Verify link invalid
        if (!$hashUid || !$token) {
            $result['message'] = __('Invalid information provided.');
            $this->view()->assign('result', $result);
            return;
        }

        $userData = Pi::user()->data()->find(array('value' => $token));
        if (!$userData) {
            $result['message'] = __('Invalid information provided.');
            $this->view()->assign('result', $result);
            return;
        }

        //$userRow = Pi::api('uclient', 'user')->get($userData['uid']);
        $userRow = Pi::api('uclient', 'user')->get($userData['uid'], array('uid'));
        $uid  = $userRow['uid'];
        if (!$uid || md5($uid) != $hashUid) {
            $result['message'] = __('Invalid information provided.');
            $this->view()->assign('result', $result);
            return;
        }

        // Verify link expire time
        $expire  =  $userData['time'] + 24 * 3600;
        $current = time();
        if ($current > $expire) {
            $result['message'] = __('Verification link is invalid: time out.');
            $this->view()->assign('result', $result);
            return;
        }

        $form = new ResetPasswordForm('find-password', 'find');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new ResetPasswordFilter('find'));
            $form->setData($data);

            if ($form->isValid()) {
                $values = $form->getData();

                // Update user account data
                Pi::api('system', 'user')->updateAccount(
                    $uid,
                    array('credential' => $values['credential-new'])
                );

                // Delete find password verify token
                Pi::user()->data()->delete($uid, 'find-password');
                $result['message'] = __('Reset password successfully.');
                $result['status']  = 1;
            }
            $this->view()->assign('result', $result);
        }

        $this->view()->assign(array(
            'form' => $form
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
}
