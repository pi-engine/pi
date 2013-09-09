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
use Pi\Acl\Acl as AclManager;
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
        // If already logged in
        if (Pi::service('user')->hasIdentity()) {
            $this->view()->assign('title', __('User login'));
            $this->view()->setTemplate('login-message');
            $this->view()->assign(array(
                'identity'  => Pi::service('user')->getIdentity(false)
            ));
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
        $configs = Pi::service('registry')->config->read('user', 'general');

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
        $this->view()->assign('title', __('User login'));
        $this->view()->assign('message', $message);
        $this->view()->assign('form', $form);
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        $uid = Pi::user()->getIdentity();
        Pi::service('session')->manager()->destroy();
        Pi::service('user')->destroy();
        Pi::service('event')->trigger('logout', $uid);

        $this->jump(
            array('route' => 'home'),
            __('You logged out successfully. Now go back to homepage.')
        );
    }

    /**
     * Process login submission
     *
     * @return void
     */
    public function processAction()
    {
        if (!$this->request->isPost()) {
            $this->jump(array('action' => 'index'), __('Invalid request.'));
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

        $configs = Pi::service('registry')->config->read('user', 'general');

        $values = $form->getData();
        $identity = $values['identity'];
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
                    5
                );

                return;
            }
        }

        $result = Pi::user()->authenticate($identity, $credential);

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

        if ($configs['rememberme'] && $values['rememberme']) {
            Pi::service('session')->manager()
                ->rememberme($configs['rememberme'] * 86400);
        }
        Pi::service('user')->bind($result->getIdentity(), 'identity');
        Pi::service('user')->setPersist($result->getData());


        if (!empty($configs['attempts'])) {
            unset($_SESSION['PI_LOGIN']);
        }

        if (empty($values['redirect'])) {
            $redirect = array('route' => 'home');
        } else {
            $redirect = urldecode($values['redirect']);
        }

        // Get user id according to identity
        $uid = $this->getModel('account')->find(
            $result->getIdentity(),
            'identity'
        )->id;

        // Trigger login event
        $rememberTime = isset($configs['rememberme'])
                      && $values['rememberme']
                      ? $values['rememberme'] * 86400
                      : 0;
        Pi::service('event')->trigger('login', array($uid, $rememberTime));

        // Check user complete profile
        $hasCompleteProfile = Pi::user()->data()->get(
            $uid,
            'profile-complete'
        );
        if (!$hasCompleteProfile) {
            $this->redirect()->toRoute(
                'user',
                array(
                    'controller' => 'register',
                    'action' => 'complete.profile',
                    'redirect' => urlencode($redirect),
                )
            );
            return;
        }

        $this->jump($redirect, __('You have logged in successfully.'), 2);
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
}
