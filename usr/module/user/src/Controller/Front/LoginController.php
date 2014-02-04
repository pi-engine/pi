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
use Module\User\Form\LoginForm;
use Module\User\Form\LoginFilter;

/**
 * User login/logout controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class LoginController extends ActionController
{
    /**
     * Login form
     *
     * @return void
     */
    public function indexAction()
    {
        if (!$this->checkAccess()) {
            return;
        }

        // If already logged in
        if (Pi::service('user')->hasIdentity()) {
            $this->jump(
                Pi::service('user')->getUrl('profile'),
                __('You have already logged in.')
            );
            return;
        }

        // Display login form
        $form = $this->getForm();
        $redirect = $this->params('redirect');
        if (null === $redirect) {
            $redirect = $this->request->getServer('HTTP_REFERER');
        }
        if (null !== $redirect) {
            $redirect = $redirect ? urlencode($redirect) : '';
            $form->setData(array('redirect' => $redirect));
        }
        $this->renderForm($form);
    }

    /**
     * Render login form
     *
     * @param LoginForm $form
     * @param string $message
     */
    protected function renderForm($form, $message = '')
    {
        $this->view()->setTemplate('login');
        $configs = Pi::user()->config('');

        if (!empty($configs['attempts'])) {
            $attempts = isset($_SESSION['PI_LOGIN']['attempts'])
                ? $_SESSION['PI_LOGIN']['attempts'] : 0;
            if (!empty($attempts)) {
                if ($attempts >= $configs['attempts']) {
                    $wait = Pi::service('session')->manager()
                        ->getSaveHandler()->getLifeTime() / 60;
                    $message = sprintf(
                        __(
                            'Login with the account is suspended,
                            please wait for %d minutes to try again.'
                        ),
                        $wait
                    );
                    $this->view()->setTemplate('login-suspended');
                } else {
                    $remaining = $configs['attempts'] - $attempts;
                    $message = sprintf(
                        __('You have %d times to try.'),
                        $remaining
                    );
                }
            }
        }
        $this->view()->assign(array(
            'title'      => __('User login'),
            'is_captcha' => $configs['login_captcha'],
            'message'    => $message,
            'form'       => $form
        ));
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        $uid = Pi::user()->getId();
        Pi::service('session')->manager()->destroy();
        Pi::service('user')->destroy();
        Pi::service('event')->trigger('logout', $uid);

        $redirect = $this->params('redirect');
        $redirect = $redirect
            ? urldecode($redirect) : array('route' => 'home');

        $this->jump(
            $redirect,
            __('You logged out successfully.')
        );
    }

    /**
     * Process login submission
     *
     * @return void
     */
    public function processAction()
    {
        if (!$this->checkAccess()) {
            return;
        }

        if (!$this->request->isPost()) {
            $this->jump(array('action' => 'index'), __('Invalid request.'), 'error');
            return;
        }

        $post = $this->request->getPost();

        $form = $this->getForm();
        $form->setData($post);
        $form->setInputFilter(new LoginFilter);

        if (!$form->isValid()) {
            $this->renderForm($form, __('Invalid input, please try again.'));

            return;
        }

        $configs        = Pi::user()->config('');
        $values         = $form->getData();
        $identityData   = (array) $values['identity'];
        $identity       = array_shift($identityData);
        $field          = '';
        if (!$configs['login_field']) {
            $field = '';
        } elseif (1 == count($configs['login_field'])) {
            $field = current($configs['login_field']);
        } elseif ($identityData) {
            $field = array_shift($identityData);
            if (!in_array($field, $configs['login_field'])) {
                $field = '';
            }
        }
        $credential = $values['credential'];

        if (!empty($configs['attempts'])) {
            $sessionLogin = isset($_SESSION['PI_LOGIN'])
                ? $_SESSION['PI_LOGIN'] : array();
            if (!empty($sessionLogin['attempts'])
                && $sessionLogin['attempts'] >= $configs['attempts']
            ) {
                $this->jump(
                    array('route' => 'home'),
                    __('You have tried too many times. Please try later.'),
                    'error'
                );

                return;
            }
        }

        $result = Pi::service('authentication')->authenticate(
            $identity,
            $credential,
            $field
        );

        if (!$result->isValid()) {
            if (!empty($configs['attempts'])) {
                if (!isset($_SESSION['PI_LOGIN'])) {
                    $_SESSION['PI_LOGIN'] = array();
                }
                $_SESSION['PI_LOGIN']['attempts'] =
                    isset($_SESSION['PI_LOGIN']['attempts'])
                        ? ($_SESSION['PI_LOGIN']['attempts'] + 1) : 1;
            }
            $message = __('Invalid credentials provided, please try again.');
            $this->renderForm($form, $message);

            return;
        }

        $uid = (int) $result->getData('id');
        try {
            Pi::service('user')->bind($uid);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->renderForm($form, $message);

            return;
        }

        Pi::service('session')->setUser($uid);

        $rememberMe = 0;
        if ($configs['rememberme'] && $values['rememberme']) {
            $rememberMe = $configs['rememberme'] * 86400;
            Pi::service('session')->manager()
                ->rememberme($rememberMe);
        }
        //Pi::service('user')->setPersist($result->getData());

        if (isset($_SESSION['PI_LOGIN'])) {
            unset($_SESSION['PI_LOGIN']);
        }

        if (empty($values['redirect'])) {
            $redirect = array('route' => 'home');
        } else {
            $redirect = urldecode($values['redirect']);
        }

        // Trigger login event
        $args = array(
            'uid'           => $uid,
            'remember_time' => $rememberMe,
        );
        Pi::service('event')->trigger('user_login', $args);

        // Set login ip
        /*
        $ipLogin = Pi::user()->getIp();
        Pi::user()->data()->set(
            $uid,
            'last_login_ip',
            $ipLogin,
            $this->getModule()
        );
        // Set login count
        Pi::user()->data()->increment($uid, 'count_login', 1);
        // Set login time
        Pi::user()->data()->set(
            $uid,
            'last_login',
            time(),
            $this->getModule()
        );
        */
        /*
        // Check user complete profile
        if ($configs['profile_complete_form']) {
            $completeProfile = Pi::api('user', 'user')->get($uid, 'level');
            if (!$completeProfile) {
                $this->redirect(
                    'user',
                    array(
                        'controller' => 'register',
                        'action'     => 'profile.complete',
                    )
                );
            }
        }
        */

        $this->jump($redirect, __('You have logged in successfully.'));
    }

    /**
     * Load login form
     *
     * @return LoginForm
     */
    protected function getForm()
    {
        $form = new LoginForm('login');
        $form->setAttribute(
            'action',
            $this->url('', array('controller' => 'login', 'action' => 'process'))
        );

        return $form;
    }

    /**
     * Check access
     *
     * @return bool
     */
    protected function checkAccess()
    {
        $loginDisable = $this->config('login_disable');
        if ($loginDisable) {
            $this->view()->setTemplate('login-disabled');
            return false;
        }

        return true;
    }
}
