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
use Module\User\Form\RegisterForm;
use Module\User\Form\RegisterFilter;
use Module\User\Form\CompleteProfileForm;
use Module\User\Form\CompleteProfileFilter;

/**
 * Register controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class RegisterController extends ActionController
{
    /**
     * Display register form
     *
     * @return array|void
     */
    public function indexAction()
    {
        // If already login
        if (Pi::service('user')->hasIdentity()) {
            $this->view()->assign('title', __('User login'));
            $this->view()->setTemplate('login-message');
            $this->view()->assign(array(
                'identity'  => Pi::service('user')->getIdentity()
            ));

            return;
        }

        $result = array(
            'status'  => 0,
            'message' => '',
        );
        list($fields, $filters) = $this->canonizeForm('custom.register');
        $form = $this->getRegisterForm($fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new RegisterFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $values = $form->getData();
                $data   = array(
                    'identity'   => $values['identity'],
                    'name'       => $values['name'],
                    'email'      => $values['email'],
                    'credential' => $values['credential'],
                );

                $uid = Pi::api('user', 'user')->addUser($data);

                // Set user role
                Pi::api('user', 'user')->setRole($uid, 'member');

                // Set user data
                $content = md5($uid . $data['name']);
                $status  = Pi::user()->data()->set(
                    $uid,
                    'register-activation',
                    $content,
                    $this->getModule()
                );
                if (!$status) {
                    $result['message'] = __('Register fail');

                    return $result;
                }

                // Send activity email
                $to  = $values['email'];
                $url = $this->url('', array(
                    'action'  => 'activate',
                    'uid'     => md5($uid),
                    'token'   => $content,
                    )
                );

                $link = Pi::url($url, true);
                list($subject, $body, $type) = $this->setMailParams(
                    $values['identity'],
                    $link
                );

                Pi::api('user', 'mail')->send($to, $subject, $body, $type);
                $result['status']  = 1;
                $result['message'] = __('Register successfully');

                return $result;
            } else {
                $result['message'] = $form->getMessages();

                return $result;
            }
        }

        $this->view()->assign(array(
            'form' => $form,
        ));
    }

    /**
     * Activate user account
     */
    public function activateAction()
    {
        $result = array(
            'status'  => 0,
            'message' => '',
        );
        $hashUid = $this->params('uid', '');
        $token   = $this->params('token', '');

        // Check link params
        if (!$hashUid || !$token) {
            $result['message'] = __('Activate link is invalid');

            return $result;
        }

        // Search user data
        $userData = Pi::user()->data()->find(array(
            'name'  => 'register-activation',
            'value' => $token,
        ));
        if (!$userData) {
            $result['message'] = __('Activate link is invalid');

            return $result;
        }

        // Check uid
        $userRow = $this->getModel('account')->find($userData['uid']);
        if (!$userRow || md5($userRow['id']) != $hashUid) {
            $result['message'] = __('Activate link is invalid');

            return $result;
        }

        // Check expire time
        $expire  = $userData['time'] + 24 * 3600;
        $current = time();
        if ($current > $expire) {
            $result['message'] = __('Activate link is invalid');

            return $result;
        }

        // Activate user
        $status = Pi::api('user', 'user')->activateUser(
            $userData['uid']
        );

        // Check result
        if (!$status) {
            $result['message'] = __('Activate link is invalid');

            return $result;
        }

        // Delete user data
        Pi::user()->data()->delete(
            $userData['uid'],
            'register-activation'
        );

        $result['status']  = 1;
        $result['message'] = __('Activate successfully');

        return $result;

    }

    /**
     * Reactive user
     *
     * @return \Pi\Mvc\Controller\ActionController
     */
    public function reactivateAction()
    {
        $uid    = _get('uid');
        $result = array(
            'status'  => 0,
            'message' => '',
        );

        if (!$uid) {
            $result['message'] = __('Resend activate mail fail');

            return $result;
        }

        // Get user info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('id', 'name', 'email', 'time_activated')
        );
        if (!$user || $user['time_activated']) {
            $result['message'] = __('Resend activate mail fail');

            return $result;
        }

        // Check user data
        $userData = Pi::user()->data()->find(array(
            'uid'    => $uid,
            'module' => $this->getModule(),
            'name'   => 'register-activation'
        ));
        if (!$userData) {
            $result['message'] = __('Resend activate mail fail');

            return $result;
        }

        // Update user data form send mail
        $content = md5($user['id'] . $user['name']);
        Pi::user()->data()->set(
            $uid,
            'register-activation',
            $content,
            $this->getModule()

        );

        // Set mail params and send verify mail
        $to = $user['email'];
        //Set verify link
        $url = $this->url('', array(
                'action' => 'activate',
                'uid'    => md5($user['id']),
                'token'  => $content
            )
        );
        $link = Pi::url($url, true);
        // Set send mail params
        list($subject, $body, $type) = $this->setMailParams(
            $user['name'],
            $link
        );

        // Send...
        Pi::api('user', 'mail')->send($to, $subject, $body, $type);

        $result['status'] = 1;
        $result['message'] = __('Resend activate mail successfully');

        return $result;

    }

    /**
     * Profile complete action
     *
     * 1. Display profile complete form
     * 2. Save user information
     * 3. Sign user data
     */
    public function completeProfileAction()
    {
        $status = 0;
        $isPost = 0;
        // Get redirect
        $redirect = $this->params('redirect', '');
        if (!$redirect) {
            $redirect = $this->url('',
                array(
                    'controller' => 'profile',
                    'action'     => 'index'
                )
            );
        } else {
            $redirect = urldecode($redirect);
        }

        // Check login
        if (!Pi::service('user')->hasIdentity()) {
            $this->redirect()->toUrl($this->url('',
                array(
                    'controller' => 'login',
                    'action'     => 'index',
                )
            ));
        }

        // Get uid
        $uid = Pi::service('user')->getId();

        // Get fields for generate form
        list($fields, $filters) = $this->canonizeForm('custom.complete.profile');
        $form = $this->getCompleteProfileForm($fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new CompleteProfileFilter($filters));
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();

                Pi::api('user', 'user')->updateUser($uid, $values);

                // Set perfect information flag in user table
                Pi::user()->data()->set(
                    $uid,
                    'complete-profile',
                    1,
                    $this->getModule()
                );
                $status = 1;
                return $this->jump(
                    $redirect,
                    __('Complete profile successfully')
                );
            }
            $isPost = 1;
        }

        $this->view()->assign(array(
            'form'    => $form,
            'status'  => $status,
            'is_post' => $isPost
        ));

        $this->view()->setTemplate('register-complete-profile');
    }

    /**
     * Get register form
     *
     * @param array $fields custom register form fields
     * @param string $name form name
     * @return \Module\User\Form\RegisterForm
     */
    protected function getRegisterForm($fields, $name = 'register')
    {
        $form = new RegisterForm($name, $fields);

        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'index'))
        );

        return $form;
    }

    /**
     * Get profile complete form
     *
     * @param array $fields custom profile complete form fields
     * @param string $name form name
     * @return \Module\User\Form\CompleteCompleteForm
     */
    protected function getCompleteProfileForm($fields, $name = 'profileComplete')
    {
        $form = new CompleteProfileForm($name, $fields);
        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'complete-profile'))
        );

        return $form;
    }

    /**
     * Canonize form
     *
     * @param $file
     * @return array
     */
    protected function canonizeForm($file)
    {
        $elements = array();
        $filters  = array();

        $file = strtolower($file);
        $configFile = sprintf(
            '%s/extra/%s/config/%s.php',
            Pi::path('usr'),
            $this->getModule(),
            $file
        );

        if (!file_exists($configFile)) {
            $configFile = sprintf(
                '%s/%s/extra/%s/config/%s.php',
                Pi::path('module'),
                $this->getModule(),
                $this->getModule(),
                $file
            );
            if (!file_exists($configFile)) {
                return;
            }
        }

        $config = include $configFile;

        foreach ($config as $value) {
            if (is_string($value)) {
                $elements[] = Pi::api('user', 'form')->getElement($value);
                $filters[]  = Pi::api('user', 'form')->getFilter($value);
            } else {
                if ($value['element']) {
                    $elements[] = $value['element'];
                }

                if ($value['filter']) {
                    $filters[] = $value['filter'];
                }
            }
        }

        return array($elements, $filters);

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
            'username'      => $username,
            'activity_link' => $link,
            'sn'            => _date(),
        );

        // Load from HTML template
        $data = Pi::service('mail')->template('activity-mail-html', $params);
        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];

        return array($subject, $body, $type);

    }
}
