<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Authentication\Result;
use Module\System\Controller\Front\LoginController as LoginControllerFront;

/**
 * Login/logout for admin
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoginController extends LoginControllerFront
{
    public function permissionException()
    {
        return true;
    }

    /**
     * Login form
     *
     * @return void
     */
    public function indexAction()
    {
        // If already logged in
        if (Pi::service('user')->hasIdentity()) {
            $this->view()->assign('title', __('Admin login'));
            $this->view()->setTemplate('login-message', '', 'front');
            $this->view()->assign(array(
                'identity'  => Pi::service('authentication')->getIdentity()
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

        $this->view()->setLayout('layout-simple');
        $this->view()->setTemplate('login', '', 'front');
    }

    /**
     * {@inheritDoc}
     */
    protected function preProcess()
    {
        if (!$this->request->isPost()) {
            $this->jump(array('action' => 'index'), __('Invalid request.'));

            return;
        }

        $configs = Pi::registry('config')->read('', 'user');

        return $configs;
    }

    /**
     * {@inheritDoc}
     */
    protected function postProcess(Result $result)
    {
        if (!$result->isValid()) {
            return $result;
        }
        $adminRoles = Pi::service('user')->getRole($result->getData('id'));
        if (!$adminRoles) {
            Pi::service('authentication')->clearIdentity();
            $result->setCode(-4)->setMessage(__('Not privileged.'));
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function renderForm($form, $message = '')
    {
        parent::renderForm($form, $message);
        $this->view()->setTemplate('login', '', 'front');
    }
}
