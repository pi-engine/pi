<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\System\Form\RegisterForm;
use Module\System\Form\RegisterFilter;

/**
 * User register operations
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RegisterController extends ActionController
{
    /**
     * Register form
     *
     * @return void
     */
    public function indexAction()
    {
        if (!$this->checkAccess()) {
            return;
        }

        // Display register form
        $form = $this->getForm();
        $this->renderForm($form);
    }

    /**
     * Render register form
     *
     * @param RegisterForm $form
     */
    protected function renderForm($form)
    {
        $this->view()->setTemplate('register');

        $this->view()->assign('title', __('User account register'));
        $this->view()->assign('form', $form);

        $this->view()->headTitle(__('Register'));
        $this->view()->headdescription(__('User account register'), 'set');
        $headKeywords = Pi::user()->config('head_keywords');
        if ($headKeywords) {
            $this->view()->headkeywords($headKeywords, 'set');
        }
    }

    /**
     * Process register submission
     *
     * @return void
     */
    public function processAction()
    {
        if (!$this->checkAccess()) {
            return;
        }

        if (!$this->request->isPost()) {
            return $this->redirect()->toRoute('', array('action' => 'index'));
        }

        $post = $this->request->getPost();
        $form = $this->getForm();
        $form->setData($post);
        $form->setInputFilter(new RegisterFilter);

        if (!$form->isValid()) {
            $this->view()->assign(
                'message',
                __('Invalid input, please try again.')
            );
            $this->renderForm($form);

            return;
        }
        $values = $form->getData();
        $data = array(
            'identity'      => $values['identity'],
            'name'          => $values['name'],
            'email'         => $values['email'],
            'credential'    => $values['credential'],
            //'active'        => 1,
            //'role'          => Acl::MEMBER,
        );
        $uid = Pi::api('user', 'system')->addUser($data);
        if (!$uid) {
            $this->view()->assign(
                'message',
                __('The account is not created in database, please try again.')
            );
            $this->renderForm($form);

            return;
        }
        Pi::api('user', 'system')->activateUser($uid);
        Pi::api('user', 'system')->setRole($uid, 'member');


        $this->view()->setTemplate('register-success');
        $this->view()->assign('title', __('Register'));
    }

    /**
     * Load register form
     *
     * @return RegisterForm
     */
    public function getForm()
    {
        $form = new RegisterForm('register');
        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'process'))
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
        if (Pi::service('module')->isActive('user')) {
            $this->redirect()->toUrl(Pi::service('user')->getUrl('register'));
            return false;
        }

        // If disabled
        $registerDisable = $this->config('register_disable');
        if ($registerDisable) {
            $this->view()->setTemplate('register-disabled');
            return false;
        }

        if (Pi::service('user')->hasIdentity()) {
            $this->redirect()->toUrl(Pi::service('user')->getUrl('profile'));
            return false;
        }

        return true;
    }
}
