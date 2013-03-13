<?php
/**
 * User authentication controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Acl\Acl as AclManager;
use Module\System\Form\LoginForm;
use Module\System\Form\LoginFilter;

class LoginController extends ActionController
{
    public function indexAction()
    {
        if (Pi::config('login_disable', 'user')) {
            $this->jump(array('route' => 'home'), __('Login is disabled. Please come back later.'), 5);
            return;
        }

        // If already logged in
        if (Pi::service('authentication')->hasIdentity()) {
            $this->view()->assign('title', __('User login'));
            $this->view()->setTemplate('login-message');
            $this->view()->assign(array(
                'identity'  => Pi::service('authentication')->getIdentity()
            ));
            return;
        }

        // Display login form
        $form = $this->getForm();
        $redirect = $this->params('redirect') ?: $this->request->getServer('HTTP_REFERER');
        if ($redirect) {
            $form->setData(array('redirect' => urlencode($redirect)));
        }
        $this->renderForm($form);
    }

    protected function renderForm($form, $message = '')
    {
        $this->view()->setTemplate('login');
        $configs = Pi::service('registry')->config->read('', 'user');

        //$message = '';
        if (!empty($configs['attempts'])) {
            /*
            $sessionLogin = Pi::service('session')->login;
            if (!empty($sessionLogin->attempts) && $sessionLogin->attempts >= $configs['attempts']) {
                $wait = Pi::service('session')->manager()->getSaveHandler()->getLifeTime() / 60;
                $message = sprintf(__('Login with the account is suspended, please wait for %d minutes to try again.'), $wait);
                $this->view()->setTemplate('login-suspended');
            } elseif (!empty($sessionLogin->attempts)) {
                $remaining = $configs['attempts'] - $sessionLogin->attempts;
                $message = sprintf(__('You have %d times to try.'), $remaining);
            }
            */
            $attempts = isset($_SESSION['PI_LOGIN']['attempts']) ? $_SESSION['PI_LOGIN']['attempts'] : 0;
            //d((array)$_SESSION);
            //d($attempts);
            if (!empty($attempts)) {
                if ($attempts >= $configs['attempts']) {
                    $wait = Pi::service('session')->manager()->getSaveHandler()->getLifeTime() / 60;
                    $message = sprintf(__('Login with the account is suspended, please wait for %d minutes to try again.'), $wait);
                    $this->view()->setTemplate('login-suspended');
                } else {
                    $remaining = $configs['attempts'] - $attempts;
                    $message = sprintf(__('You have %d times to try.'), $remaining);
                }
            }
        }
        //$form->assign($this->view);
        $this->view()->assign('title', __('User login'));
        $this->view()->assign('message', $message);
        $this->view()->assign('form', $form);
    }

    public function logoutAction()
    {
        //$this->view()->assign('title', __('Logout'));
        //Pi::service('session')->manager()->destroy();
        Pi::service('session')->manager()->getStorage()->clear();
        $this->jump(array('route' => 'home'), __('You logged out successfully. Now go back to homepage.'));
    }

    public function processAction()
    {
        if (Pi::config('login_disable', 'user')) {
            $this->jump(array('route' => 'home'), __('Login is closed. Please try later.'), 5);
            return;
        }

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
            //$this->view()->assign('message', __('Invalid input, please try again.'));
            return;
        }

        $configs = Pi::service('registry')->config->read('', 'user');

        $values = $form->getData();
        $identity = $values['identity'];
        $credential = $values['credential'];

        /*
        if (!empty($configs['attempts'])) {
            $sessionLogin = Pi::service('session')->login;
            if (!empty($sessionLogin->attempts) && $sessionLogin->attempts >= $configs['attempts']) {
                $this->jump(array('route' => 'home'), __('You have tried too many times. Please try later.'), 5);
                return;
            }
        }
        */
        if (!empty($configs['attempts'])) {
            $sessionLogin = isset($_SESSION['PI_LOGIN']) ? $_SESSION['PI_LOGIN'] : array();
            if (!empty($sessionLogin['attempts']) && $sessionLogin['attempts'] >= $configs['attempts']) {
                $this->jump(array('route' => 'home'), __('You have tried too many times. Please try later.'), 5);
                return;
            }
        }

        $result = Pi::service('authentication')->authenticate($identity, $credential);

        if (!$result->isValid()) {
            /*
            if (!empty($configs['attempts'])) {
                $sessionLogin = Pi::service('session')->login;
                $sessionLogin->attempts = isset($sessionLogin->attempts) ? ($sessionLogin->attempts + 1) : 1;
            }
            */
            if (!empty($configs['attempts'])) {
                if (!isset($_SESSION['PI_LOGIN'])) {
                    $_SESSION['PI_LOGIN'] = array();
                }
                $_SESSION['PI_LOGIN']['attempts'] = isset($_SESSION['PI_LOGIN']['attempts']) ? ($_SESSION['PI_LOGIN']['attempts'] + 1) : 1;
            }
            $message = __('Invalid credentials provided, please try again.');
            $this->renderForm($form, $message);
            return;
        }

        if ($configs['rememberme'] && $values['rememberme']) {
            Pi::service('session')->manager()->rememberme($configs['rememberme'] * 86400);
        }
        Pi::service('authentication')->wakeup($result->getIdentity());
        Pi::service('event')->trigger('login', $result->getIdentity());

        if (!empty($configs['attempts'])) {
            /*
            $sessionLogin = Pi::service('session')->login;
            unset($sessionLogin);
            */
            unset($_SESSION['PI_LOGIN']);
        }

        if (empty($values['redirect'])) {
            $redirect = array('route' => 'home');
        } else {
            $redirect = urldecode($values['redirect']);
        }

        $this->jump($redirect, __('You have logged in successfully.'), 2);

        //$this->view()->setTemplate('login-success');
        //$this->view()->assign('title', __('Login'));
        //return $this->redirect()->toRoute('home');
    }

    // login form
    public function getForm()
    {
        $form = new LoginForm('login');
        $form->setAttribute('action', $this->url('', array('action' => 'process')));

        return $form;
    }
}
