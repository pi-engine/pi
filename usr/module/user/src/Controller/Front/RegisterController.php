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
use Pi\Acl\Acl;
use Module\User\Form\RegisterForm;
use Module\User\Form\RegisterFilter;
use Module\User\Form\ProfileCompleteForm;
use Module\User\Form\ProfileCompleteFilter;


/**
 * Register controller for user
 *
 * Tasks:
 *
 * 1. Register form
 * 2. Send email
 * 3. Add a new user
 * 4. Complete register
 *
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
                'identity'  => Pi::service('user')->getIdentity(false)
            ));
            return;
        }

        list($fields, $filters) = $this->canonizeForm('register.form');
        $form = $this->getRegisterForm($fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new RegisterFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $values     = $form->getData();

                $data = array(
                    'identity'   => $values['identity'],
                    'name'       => $values['name'],
                    'email'      => $values['email'],
                    'credential' => $values['credential'],
                );

                $result = Pi::api('user', 'user')->addUser($data);
                $uid    = $result[0];

                // Set user role
                $this->createRole($uid);
                Pi::api('user', 'user')->setRole($uid, Acl::MEMBER);

                // Set user data
                $content = md5(uniqid($uid . $data['name']));
                $result = Pi::user()->data()->set(
                    $uid,
                    'register-activation',
                    $content
                );
                if (!$result) {
                    $message = $result['message'];
                    $this->jump(
                        array('action' => 'index'),
                        $message
                    );
                }

                // Send activity email
                $to = $values['email'];
                $url = $this->url('', array(
                    'action' => 'activate',
                    'id'     => md5($uid),
                    'token'  => $content,
                    )
                );

                $link = Pi::url($url, true);
                list($subject, $body, $type) = $this->setMailParams(
                    $values['username'],
                    $link
                );

                Pi::api('user', 'mail')->send($to, $subject, $body, $type);
                $this->redirect()->toUrl($this->url('',
                    array(
                        'action' => 'display',
                        'type'   => 'register',
                        'uid'    => $uid
                    )
                ));
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
        $data = array(
            'title'  => __('Register'),
            'status' => false,
        );

        $key   = $this->params('id', '');
        $token = $this->params('token', '');

        if (!$token || !$token) {
            return $this->jumpTo404('Required resource is not found');
        }

        /*
        $userData = Pi::api('user', 'userdata')
                    ->getData(array('content' => $token));
        $userData = array_pop($userData);
        */
        $userData = Pi::user()->data()->find(array(
            'name'  => 'register-activation',
            'value' => $token,
        ));
        if ($userData) {
            $hashUid = md5($userData['uid']);
            $userRow = $this->getModel('account')->find($userData['uid']);

            if ($userRow && $hashUid == $key) {
                $expire  = $userData['time'] + 24 * 3600;
                $current = time();
                if ($current < $expire) {
                    // Activate user
                    $result = Pi::api('user', 'user')->activateUser(
                        $userData['uid']
                    );

                    if ($result) {
                        // Delete user data
                        Pi::user()->data()->delete(
                            $userData['uid'],
                            'register-activation'
                        );
                        $data['status'] = true;
                        $data['title']  = __('Register done');
                    }
                }
            }
        }

        $this->view()->assign('data', $data);
    }

    /**
     * Display register relate information
     */
    public function displayAction()
    {
        $type = $this->params('type', '');
        $uid  = $this->params('uid', '');
        $data = array();

        switch (strtolower($type)) {
            case 'register':
                $title = __('Register Activation');
                $data['uid'] = $uid;
                break;
            default:
                 return $this->jumpTo404('Required resource is not found');
        }

        $this->view()->assign(array(
            'type'  => $type,
            'title' => $title,
            'data'  => $data,
        ));
    }

    /**
     * Reactive user
     *
     * @return \Pi\Mvc\Controller\ActionController
     */
    public function reactivateAction()
    {
        $uid = $this->params('uid', '');

        if (!$uid) {
            return $this->jumpTo404('An error occur');
        }

        // Get account info
        $account = Pi::api('user', 'user')->get(
            $uid,
            array('id', 'name', 'email')
        );
        if (!$account) {
           return $this->jumpTo404('An error occur');
        }

        // Set user data form send mail
        $content = md5(uniqid($account['id'] . $account['name']));
        Pi::user()->data()->set($uid, 'register-activation', $content);

        // Set mail params and send verify mail
        $to = $account['email'];
        //Set verify link
        $url = $this->url('', array(
                'action' => 'activate',
                'id'     => md5($account['id']),
                'token'  => $content
            )
        );
        $link = Pi::url($url, true);
        // Set send mail params
        list($subject, $body, $type) = $this->setMailParams(
            $account['name'],
            $link
        );
        // Send...
        Pi::api('user', 'mail')->send($to, $subject, $body, $type);

        // Display result message
        $this->redirect()->toUrl($this->url('',
            array('action' => 'display', 'type' => 'register', 'uid' => $uid)
        ));
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
                    'action'     => 'home'
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
        $uid = Pi::service('user')->getIdentity();

        // Get fields for generate form
        list($fields, $filters) = $this->canonizeForm('profile.complete');
        $form = $this->getProfileCompleteForm($fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new ProfileCompleteFilter($filters));
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();

                Pi::api('user', 'user')->updateUser($uid, $values);

                // Set perfect information flag in user table
                Pi::user()->data()->set($uid, 'profile-complete', 1);
                $status = 1;
                return $this->jump(
                    $redirect,
                    __('Perfect information successfully')
                );
            }
            $isPost = 1;
        }

        $this->view()->assign(array(
            'form'   => $form,
            'status' => $status,
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
     * @return \Module\User\Form\ProfileCompleteForm
     */
    protected function getProfileCompleteForm($fields, $name = 'profileComplete')
    {
        $form = new ProfileCompleteForm($name, $fields);
        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'complete-profile'))
        );

        return $form;
    }

    /**
     * Canonize data to element
     *
     * @param string $file
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

        $data = include $configFile;

        foreach ($data as $value) {
            if ($value['element']) {
                $elements[] = $value['element'];
            }

            if ($value['filter']) {
                $filters[] = $value['filter'];
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
