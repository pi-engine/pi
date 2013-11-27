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
use Module\User\Form\ProfileCompleteForm;
use Module\User\Form\ProfileCompleteFilter;
use Module\User\Form\ResendActivateMailForm;
use Module\User\Form\ResendActivateMailFilter;

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
        if (Pi::user()->getId()) {
            return $this->redirect(
                '',
                array(
                    'controller'    => 'profile',
                    'action'        => 'index'
                )
            );
        }

        $result = array(
            'status'  => 0,
        );

        // Get register form
        $registerFormConfig = $this->config('register_form');
        list($fields, $filters) = $this->canonizeForm($registerFormConfig, 'register');
        $form = $this->getRegisterForm($fields);
        $registeredSource = _get('app') ? : '';
        $form->setData(array('registered_source' => $registeredSource));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new RegisterFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $registerCompleteFormConfig = $this->config('register_complete_form');
                if ($registerCompleteFormConfig) {
                    // Display custom complete form
                    $values = $form->getData();
                    list($fields, $filters) = $this->canonizeForm(
                        $registerCompleteFormConfig,
                        'register_complete'
                    );
                    $form = $this->getRegisterForm(
                        $fields,
                        'register_complete'
                    );
                    unset($values['submit']);
                    $form->setData($values);
                    $form->setAttributes(array(
                        'action' => $this->url('', array(
                                'controller' => 'register',
                                'action'     => 'complete'
                            )
                        ),
                    ));

                    $this->view()->assign(array(
                        'form'     => $form,
                        'complete' => 1
                    ));
                    return;
                } else {
                    // Complete register
                    $values = $form->getData();
                    $values['last_modified'] = time();
                    $values['ip_register']   = Pi::user()->getIp();
                    $uid = Pi::api('user', 'user')->addUser($values);
                    if (is_array($uid)) {
                        $this->view()->assign(array(
                            'result' => $result,
                            'from'   => $form,
                        ));

                        return;
                    }

                    // Set user role
                    Pi::api('user', 'user')->setRole($uid, 'member');

                    // Set user data
                    $content = md5($uid . $values['name']);
                    Pi::user()->data()->set(
                        $uid,
                        'register-activation',
                        $content,
                        $this->getModule()
                    );

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

                    $message = Pi::service('mail')->message($subject, $body, $type);
                    $message->addTo($to);
                    $transport = Pi::service('mail')->transport();
                    $transport->send($message);
                    $result['uid']     = $uid;
                    $result['status']  = 1;
                }
            }
            $this->view()->assign('result', $result);
        }

        $this->view()->assign(array(
            'form'   => $form,
        ));
    }

    public function completeAction()
    {
        $registerCompleteFormConfig = $this->config('register_complete_form');
        if (!$registerCompleteFormConfig ||
            !$this->request->isPost()
        ) {
            return $this->redirect(
                '',
                array(
                    'controller'    => 'register',
                    'action'        => 'index'
                )
            );
        }

        $result = array(
            'status' => 0,
        );
        $post = $this->request->getPost();
        list($fields, $filters) = $this->canonizeForm(
            $registerCompleteFormConfig,
            'register_complete'
        );
        $form = $this->getRegisterForm(
            $fields,
            'register_complete'
        );
        unset($post['submit']);
        $form->setData($post);
        $form->setInputFilter(new RegisterFilter($filters));
        $form->setAttributes(array(
            'action' => $this->url('', array(
                    'controller' => 'register',
                    'action' => 'complete'
                )),
        ));
        if ($form->isValid()) {
            $values = $form->getData();
            $values = $this->canonizeUser($values, 'work');
            $values['last_modified'] = time();
            $values['ip_register']   = Pi::user()->getIp();
            $uid = Pi::api('user', 'user')->addUser($values);
            if (is_array($uid)) {
                $this->view()->assign(array(
                    'result' => $result,
                    'from'   => $form,
                ));

                return;
            }
            // Set user role
            Pi::api('user', 'user')->setRole($uid, 'member');

            // Set user data
            $content = md5($uid . $values['name']);
            Pi::user()->data()->set(
                $uid,
                'register-activation',
                $content,
                $this->getModule()
            );

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

            $message = Pi::service('mail')->message($subject, $body, $type);
            $message->addTo($to);
            $transport = Pi::service('mail')->transport();
            $transport->send($message);
            $result['uid']     = $uid;
            $result['status']  = 1;
            $result['email']   = $to;
        }

        $this->view()->assign(array(
            'result'   => $result,
            'complete' => 1,
            'form'     => $form
        ));

        $this->view()->setTemplate('register-index');
    }

    /**
     * Activate user account
     */
    public function activateAction()
    {
        if (Pi::user()->getId()) {
            return $this->redirect(
                '',
                array(
                    'controller'    => 'profile',
                    'action'        => 'index'
                )
            );
        }

        $result = array(
            'status'  => 0,
            'message' => '',
        );
        $hashUid = $this->params('uid', '');
        $token   = $this->params('token', '');

        // Check link params
        if (!$hashUid || !$token) {
            $result['message'] = __('Activate link is invalid');
            $this->view()->assign('result', $result);
            return;
        }

        // Search user data
        $userData = Pi::user()->data()->find(array(
            'name'  => 'register-activation',
            'value' => $token,
        ));
        if (!$userData) {
            $result['message'] = __('Activate link is invalid');
            $this->view()->assign('result', $result);
            return;
        }

        // Check uid
        $userRow = $this->getModel('account')->find($userData['uid']);
        if (!$userRow || md5($userRow['id']) != $hashUid) {
            $result['message'] = __('Activate link is invalid');
            $this->view()->assign('result', $result);
            return;
        }

        // Check expire time
        $expire  = $userData['time'] + 24 * 3600;
        $current = time();
        if ($current > $expire) {
            $result['message'] = __('Activation link is invalid');
            $this->view()->assign('result', $result);
            return;
        }

        // Activate user
        $status = Pi::api('user', 'user')->activateUser($userData['uid']);

        // Check result
        if (!$status) {
            $result['message'] = __('Activation link is invalid');
            $this->view()->assign('result', $result);
            return;
        }

        // Delete user data
        Pi::user()->data()->delete(
            $userData['uid'],
            'register-activation'
        );

        $result['status']  = 1;
        $result['message'] = __('Activate successfully');

        $this->view()->assign('result', $result);

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
            'message' => __('Resend activate mail failed'),
        );

        if (!$uid) {
            return $result;
        }

        // Get user info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('id', 'name', 'email', 'time_activated')
        );
        if (!$user || $user['time_activated']) {
            return $result;
        }

        // Check user data
        $userData = Pi::user()->data()->find(array(
            'uid'    => $uid,
            'module' => $this->getModule(),
            'name'   => 'register-activation'
        ));
        if (!$userData) {
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
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($to);
        $transport = Pi::service('mail')->transport();
        $transport->send($message);

        $result['status']  = 1;
        $result['uid']     = $uid;
        $result['message'] = __('Resend activate mail successfully');

        return $result;

    }

    public function resendActiveMailAction()
    {
        $form = new ResendActivateMailForm();
        $this->view()->setTemplate('register-resend-activate-mail');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new ResendActivateMailFilter());

            if ($form->isValid()) {
                $values = $form->getData();

                // Check email
                $row = $this->getModel('account')->find($values['email'], 'email');
                if (!$row) {
                    $result['status'] = 0;
                    $result['message'] = __('Email not exist');
                }

                if ($row->time_activated) {
                    $result['status'] = 0;
                    $result['message'] = __('Account has activated');
                }
                if (isset($result['status'])) {
                    $this->view()->assign(array(
                        'form'   => $form,
                        'result' => $result,
                    ));

                    return;
                }

                $uid = $row->id;
                // Update user data form send mail
                $content = md5($row['id'] . $row['name']);
                Pi::user()->data()->set(
                    $uid,
                    'register-activation',
                    $content,
                    $this->getModule()
                );

                // Set mail params and send verify mail
                $to = $values['email'];
                //Set verify link
                $url = $this->url('', array(
                        'action' => 'activate',
                        'uid'    => md5($row->id),
                        'token'  => $content
                    )
                );
                $link = Pi::url($url, true);
                $params = array(
                    'username'      => $row->identity,
                    'activity_link' => $link,
                    'sn'            => _date(),
                );

                // Load from HTML template
                $data = Pi::service('mail')->template('activity-mail-html', $params);
                // Send...
                $message = Pi::service('mail')->message(
                    $data['subject'],
                    $data['body'],
                    $data['format']
                );
                $message->addTo($to);
                $transport = Pi::service('mail')->transport();
                $transport->send($message);

                $result['status']  = 1;
                $result['message'] = __('Resend activate mail successfully');
            } else {
                $result['status'] = 0;
                $result['message'] = __('Input error');
            }

            $this->view()->assign('result', $result);
        }

        $this->view()->assign('form', $form);
    }

    /**
     * Profile complete action
     *
     * 1. Display profile complete form
     * 2. Save user information
     * 3. Sign user data
     */
    public function profileCompleteAction()
    {
        $result = array(
            'status' => 0,
        );

        $profileCompleteFormConfig = $this->config('profile_complete_form');
        if (!$profileCompleteFormConfig) {
            return $this->redirect(
                '',
                array(
                    'controller'    => 'profile',
                    'action'        => 'index'
                )
            );
        }
        Pi::service('authentication')->requireLogin();
        $uid = Pi::user()->getId();

        // Get fields for generate form
        list($fields, $filters) = $this->canonizeForm(
            $profileCompleteFormConfig,
            'profile_complete'
        );
        $form = $this->getProfileCompleteForm($fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new ProfileCompleteFilter($filters));
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();
                $values['level'] = 1;
                $values['last_modified'] = time();
                Pi::api('user', 'user')->updateUser($uid, $values);

                //@FIXME: Temporarily customized
                //@TODO: To be refactored with event/listener
                if ($values['source_id']) {
                    $uri = 'http://www.eefocus.com/passport/api.php';
                    $params = array(
                        'act' => 'join',
                        'uid' => $uid,
                        'pid' => $values['source_id']
                    );
                    Pi::service('remote')->get($uri, $params);
                }

                return $this->redirect(
                    '',
                    array(
                    'controller' => 'profile',
                    'action'     => 'index'
                    )
                );
            } else {
                $this->view()->assign('result', $result);
            }
        }

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('register-profile-complete');
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
    protected function getProfileCompleteForm($fields, $name = 'profileComplete')
    {
        $form = new ProfileCompleteForm($name, $fields);
        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'profile-complete'))
        );

        return $form;
    }

    /**
     * Canonize form
     *
     * @param $file
     * @return array
     */
    protected function canonizeForm($fileName, $type)
    {
        $elements = array();
        $filters  = array();
        if ($type == 'register') {
            if (!$fileName) {
                $file = sprintf(
                    '%s/register.php',
                    Pi::path('usr/module/user/config')
                );
            } else {
                $file = sprintf(
                    '%s/module/user/config/%s.php',
                    Pi::path('custom'),
                    $fileName
                );
            }
        }
        if (($type == 'profile_complete' || $type == 'register_complete') &&
            $fileName
        ) {
            $file = sprintf(
                '%s/module/user/config/%s.php',
                Pi::path('custom'),
                $fileName
            );
        }

        $config = include $file;
        $meta = Pi::registry('field', 'user')->read();
        foreach ($config as $value) {
            if (is_string($value)) {
                if (isset($meta[$value]) &&
                    $meta[$value]['type'] == 'compound'
                ) {
                    $compoundElements = Pi::api('user', 'form')->getCompoundElement($value);
                    foreach ($compoundElements as $element) {
                        if ($element) {
                            $elements[] = $element;
                        }
                    }
                    $compoundFilters = Pi::api('user', 'form')->getCompoundFilter($value);
                    foreach ($compoundFilters as $filter) {
                        if ($filter) {
                            $filters[] = $filter;
                        }
                    }
                } else {
                    $element    = Pi::api('user', 'form')->getElement($value);
                    $filter     = Pi::api('user', 'form')->getFilter($value);
                    if ($element) {
                        $elements[] = $element;
                    }
                    if ($filter) {
                        $filters[] = $filter;
                    }
                }
            } else {
                if ($value['element']) {
                    $elements[] = $value['element'];
                }
                if (isset($value['filter']) && $value['filter']) {
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

    /**
     * Canonize user compound
     *
     * @param $data
     * @param $type
     * @return mixed
     */
    protected function canonizeUser($data, $type)
    {
        if ($type == 'work') {
            $workMeta = Pi::registry('compound_field', 'user')->read($type);
            $workMeta = array_keys($workMeta);
            foreach ($workMeta as $field) {
                if (isset($data[$field])) {
                    $data[$type][0][$field] = $data[$field];
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }
}
