<?php
/**
 * User registration controller
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
use Pi\Acl\Acl;
use Module\System\Form\RegisterForm;
use Module\System\Form\RegisterFilter;

class RegisterController extends ActionController
{
    public function indexAction()
    {
        if (Pi::config('register_disable', 'user')) {
            $this->jump(array('route' => 'home'), __('Registration is disabled. Please come back later.'), 5);
            return;
        }

        // Display register form
        $form = $this->getForm();
        $this->renderForm($form);
    }

    protected function renderForm($form)
    {
        $this->view()->setTemplate('register');

        $this->view()->assign('title', __('User account register'));
        $this->view()->assign('form', $form);
    }

    public function processAction()
    {
        if (Pi::config('register_disable', 'user')) {
            $this->redirect()->toRoute('home');
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
            $this->view()->assign('message', __('Invalid input, please try again.'));
            $this->renderForm($form);
            return;
        }
        $values = $form->getData();
        $data = array(
            'identity'      => $values['identity'],
            'name'          => $values['name'],
            'email'         => $values['email'],
            'credential'    => $values['credential'],
            'active'        => 1,
            'role'          => Acl::MEMBER,
        );
        $result = Pi::service('api')->system(array('member', 'add'), $data);
        if (!$result['id']) {
            $this->view()->assign('message', __('The account is not created in database, please try again.'));
            $this->renderForm($form);
            return;
        }

        /*
        $userRow = Pi::model('user')->createRow($data);
        $userRow->prepare()->save();
        if (!$userRow->id) {
            $this->view()->assign('message', __('The account is not created in database, please try again.'));
            $this->renderForm($form);
            return;
        }
        // Create user role
        $roleRow = Pi::model('user_role')->createRow(array(
            'user'  => $userRow->id,
            'role'  => Acl::MEMBER,
        ));
        $roleRow->save();
        */

        $this->view()->setTemplate('register-success');
        $this->view()->assign('title', __('Register'));
    }

    // register form
    public function getForm()
    {
        $form = new RegisterForm('register');
        $form->setAttribute('action', $this->url('', array('action' => 'process')));

        return $form;
    }
}
