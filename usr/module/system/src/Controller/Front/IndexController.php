<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Public index controller
 */
class IndexController extends ActionController
{
    /**
     * Default action if none provided
     */
    public function indexAction()
    {
        //return $this->jumpTo404('Demo for 404');
        //return $this->jumpToDenied('Demo for denied');
        //return $this->jumpToException('Demo for 503', 503);

        //$this->flashMessenger()->addMessage('Test for flash messenger.');
        //$this->flashMessenger('Test for flash messenger.');

        // Save statistics
        if (Pi::service('module')->isActive('statistics')) {
            Pi::api('log', 'statistics')->save('system', 'index');
        }

        // Set view
        $this->view()->setTemplate('system-home');
    }

    /**
     * Action called if matched action is denied
     *
     * @return self
     */
    public function notAllowedAction()
    {
        return $this->jumpToDenied('Access to resource is denied.');
    }

    /**
     * Action called if matched action does not exist
     *
     * @return self
     */
    public function notFoundAction()
    {
        return $this->jumpTo404('Required resource is not found.');
    }

    /**
     * Get user data
     */
    public function userAction()
    {
        $params = $this->params()->fromRoute();
        if (Pi::service('user')->hasIdentity()) {
            $uid    = Pi::service('user')->getId();
            $name   = Pi::service('user')->getUser()->get('name');
            $avatar = Pi::service('user')->getPersist('avatar-mini');
            if (!$avatar) {
                $avatar = Pi::service('user')->avatar($uid, 'mini');
                Pi::service('user')->setPersist('avatar-mini', $avatar);
            }
            $user = [
                'uid'     => $uid,
                'name'    => $name,
                'avatar'  => $avatar,
                'profile' => Pi::service('user')->getUrl('profile', $params),
                'logout'  => Pi::service('authentication')->getUrl('logout', $params),
                'message' => Pi::service('user')->message()->getUrl(),
            ];
        } else {
            $user = [
                'uid' => 0,
            ];
        }

        return $user;
    }

    public function modalAction()
    {
        Pi::service('log')->mute();


        if (!empty(Pi::user()->config('login_description'))) {
            $descriptionLogin = Pi::user()->config('login_description');
            $titleLogin       = Pi::user()->config('login_modal_title');
            $classLogin       = 'col-lg-6';
        } else {
            $descriptionLogin = '';
            $classLogin       = 'col-lg-12';
        }

        if (!empty(Pi::user()->config('register_description'))) {
            $descriptionRegister = Pi::user()->config('register_description');
            $titleRegister       = Pi::user()->config('register_modal_title');
            $classRegister       = 'col-lg-6';
        } else {
            $descriptionRegister = '';
            $classRegister       = 'col-lg-12';
        }

        /*
         * Login form
         */
        $processPath = Pi::service('url')->assemble('user', ['module' => 'user', 'controller' => 'login', 'action' => 'process']);
        $loginForm   = Pi::api('form', 'user')->loadForm('login', false, true);
        $loginForm->setAttribute('action', Pi::url($processPath));

        /**
         * Register form
         */
        $processPath  = Pi::service('url')->assemble('user', ['module' => 'user', 'controller' => 'register']);
        $registerForm = Pi::api('form', 'user')->loadForm('register', false, true);
        $registerForm->get('submit')->setAttribute('class', $registerForm->get('submit')->getAttribute('class') . ' w-100');
        $registerForm->setAttribute('action', Pi::url($processPath));

        if ($registerForm->has('redirect') && !$registerForm->get('redirect')->getValue()) {

            if($_SERVER['HTTP_REFERER'] && !strpos($_SERVER['HTTP_REFERER'], 'user/register')) {
                $redirect = $_SERVER['HTTP_REFERER'];
            } else {
                $redirect = Pi::url('');
            }

            $registerForm->get('redirect')->setValue($redirect);
        }

        if (Pi::engine()->application()->getResponse()->getStatusCode() == 404) {
            if ($loginForm->has('redirect')) {
                $loginForm->remove('redirect');
            }
            if ($registerForm->has('redirect')) {
                $registerForm->remove('redirect');
            }
        }


        /** @var \Pi\Mvc\Controller\Plugin\View $view */
        $view = $this->view();

        $this->view()->assign([
            'registerForm'   => $registerForm,
            'loginForm' => $loginForm,
            'titleLogin'    => $titleLogin,
            'titleRegister' => $titleRegister,
            'classLogin' => $classLogin,
            'classRegister' => $classRegister,
            'descriptionLogin' => $descriptionLogin,
            'descriptionRegister' => $descriptionRegister,
        ]);

        $view->setTemplate('login-register-modal-ajax.phtml');
        $view->setLayout('layout-content');
    }
}
