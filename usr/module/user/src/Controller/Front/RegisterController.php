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
        list($fields, $filters) = $this->canonizeForm('register.form');
        $form = $this->getForm($fields);

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

                $uid = Pi::api('user', 'user')->addUser($data);

                // Set user role
                $this->createRole($uid);
                // Set user data
                $result = Pi::api('user', 'userdata')
                    ->setData($uid, 'register', 'user');
                if (!$result) {
                    $message = $result['message'];
                    $this->jump(
                        array('action' => 'index'),
                        $message
                    );
                }

                // Send activity email
                $to = $values['email'];
                $baseLocation = Pi::host()->get('baseLocation');
                $url = $this->url('', array(
                    'action' => 'activate',
                    'id'     => md5($uid),
                    'token'  => $result['token']
                    )
                );

                $link = $baseLocation . $url;
                list($subject, $body, $type) = $this->setMailParams(
                    $values['username'],
                    $link
                );
                Pi::api('user', 'mail')->send($to, $subject, $body, $type);
                $this->redirect()->toUrl($this->url('',
                    array('action' => 'display', 'type' => 'register', 'uid' => $uid)
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

        $userData = Pi::api('user', 'userdata')->getDataByContent($token);

        if ($userData) {
            $hashUid = md5($userData['uid']);
            $userRow = $this->getModel('account')->find($userData['uid'], 'id');

            if ($userRow && $hashUid == $key) {
                $expire  = $userData['time'] + 24 * 3600;
                $current = time();
                if ($current < $expire) {
                    // Activate user
                    $result = Pi::api('user', 'user')
                        ->activateUser($userData['uid']);

                    if ($result) {
                        // Delete user data
                        Pi::api('user', 'userdata')->deletData($userData['id']);
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

        $account = Pi::api('user', 'user')->getAccount($uid);
        if (!$account) {
           return $this->jumpTo404('An error occur');
        }

        $result = Pi::api('user', 'userdata')
            ->setData($uid, 'register', 'user');

        $to = $account['email'];
        $baseLocation = Pi::host()->get('baseLocation');
        $url = $this->url('', array(
                'action' => 'activate',
                'id'     => md5($account['id']),
                'token'  => $result['token']
            )
        );

        $link = $baseLocation . $url;
        list($subject, $body, $type) = $this->setMailParams(
            $account['username'],
            $link
        );
        Pi::api('user', 'mail')->send($to, $subject, $body, $type);
        $this->redirect()->toUrl($this->url('',
            array('action' => 'display', 'type' => 'register', 'uid' => $uid)
        ));
    }

    public function prefectInformationAction()
    {
        list($fields, $filters) = $this->canonizeForm('prefect.information');
        $form = $this->getForm($fields);
        $this->view()->assign(array(
            'form' => $form,
        ));

        if ($this->request->isPost()) {

        }

        $this->view()->setTemplate('register-prefect-information');
    }

    public function testAction()
    {
        $this->view()->setTemplate(false);
        return $this->jump(
            array('action' => 'index'),
            'just test'
        );
    }

    /**
     * Create role for register user
     *
     * @param $uid
     * @param string $role
     * @param string $section
     * @return mixed
     */
    protected function createRole($uid, $role = Acl::MEMBER, $section = 'front')
    {
        $roleModel = $this->getModel('role');
        $row = $roleModel->createRow(array(
            'uid'     => $uid,
            'role'    => $role,
            'section' => $section,
        ));
        return $row->save();
    }


    /**
     * Get register form
     *
     * @return \Module\User\Form\RegisterForm
     */
    protected function getForm($fields)
    {
        $form = new RegisterForm('register', $fields);

        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'index'))
        );

        return $form;
    }

    /**
     * Canonize data to element
     *
     * @param $data
     * @return array
     */
    protected function canonizeForm($file)
    {
        $elements = array();
        $filters  = array();

        $configFile = sprintf(
            '%s/extra/%s/config/%s.php',
            Pi::path('usr'),
            $this->getModule(),
            $file
        );

        if (!file_exists($configFile)) {
            return;
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
