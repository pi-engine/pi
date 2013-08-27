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
use Module\User\Form\EmailForm;
use Module\User\Form\EmailFilter;

/**
 * Feature list:
 * 1. Change email
 * 2. Send verify email code
 */
class EmailController extends ActionController
{
    /**
     * 1. Display change email form
     * 2. Send verify code
     * 3. Check verify code
     * 4. change email complete
     *
     * @return array|void
     */
    public function indexAction()
    {
        $identity = Pi::service('user')->getUser()->identity;

        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }

        $email = Pi::service('user')->getUser()->email;

        // Set display account message
        $user = array(
            __('Username')  => $identity,
            __('Email')     => $email,
        );

        $form = new EmailForm('change-email');
        if ($this->request->isPost()) {
            // Process new email
            $data = $this->request->getPost();
            $form->setInputFilter(new EmailFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $uid = Pi::service('user')->getUser()->id;

                $content = md5($uid . time());
                $result = Pi::api('user', 'userdata')
                    ->setData($uid, $this->getModule(), 'change-email', $content);

                if (!$result) {
                    $this->jump($this->url(
                        'default',
                        array('controller' => 'email', 'action' => 'index')),
                        __('Change email error'),
                        3
                    );
                }

                $to = $values['email-new'];
                $baseLocation = Pi::host()->get('baseLocation');
                $url = $this->url('', array(
                        'action'=> 'process',
                        'id'    => md5($uid),
                        'token' => $content,
                        'new'   => urlencode($values['email-new'])
                    )
                );

                $link = $baseLocation . $url;
                list($subject, $body, $type) = $this->setMailParams(
                    $values['username'],
                    $link
                );
                Pi::api('user', 'mail')->send($to, $subject, $body, $type);

                $this->send($to, $link, $identity);
                $this->redirect('default',array('controller' => 'email', 'action' => 'send.complete'));

            } else {
                $form->setData(array('identity' => $identity));
                $message = __('Invalid data, please check and re-submit.');
            }

        } else {
            $form->setData(array('identity' => $identity));
            $message = '';
        }

        $title = __('Change Email');
        $this->view()->assign(array(
            'title'   => $title,
            'form'    => $form,
            'message' => $message,
        ));
    }

    /**
     * 1. Verify token
     * 2. Change email
     * 3. Display result message
     */
    public function processAction()
    {
        $key      = $this->params('id', '');
        $token    = $this->params('token', '');
        $newEmail = urldecode($this->params('new', ''));

        $data = array(
            'message' => __('Change email fail!'),
            'status'  => 0,
        );

        // Assign title to template
        $this->view()->assign('title', __('Change email'));

        // Verify link invalid
        if (!key || !$token || !$newEmail) {
            $this->view()->assign('data', $data);
            return;
        }

        $userData = Pi::api('user', 'userdata')
                    ->getData(array('content' => $token));
        $userData = array_pop($userData);
        if ($userData) {
            $hashUid = md5($userData['uid']);
            $userRow = $this->getModel('account')->find($userData['uid'], 'id');

            if ($userRow && $hashUid == $key) {
                $expire  = $userData['time'] + 24 * 3600;
                $current = time();

                // Valid verify link
                if ($current < $expire) {
                    // Change email
                    $user = array(
                        'email' => $newEmail,
                    );

                    Pi::api('user', 'user')->updateUser($user, $userData['uid']);
                }

                // Delete change email verify link
                Pi::api('user', 'userdata')->deletData($userData['id']);
                $data['message'] = __('Change success');
                $data['status']  = 1;
                $this->view()->assign('data', $data);
            }
        }
    }

    /**
     * Display send verify mail message
     *
     */
    public function sendCompleteAction()
    {
        $changeEmailLink = $this->url('default', array('controller' => 'email', 'action' => 'index' ));
        $this->view()->assign('changeEmailLink', $changeEmailLink);
        $this->view()->setTemplate('change-email-success');
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
        $data = Pi::service('mail')->template('change-email-html', $params);
        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];

        return array($subject, $body, $type);
    }
}
