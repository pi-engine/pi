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
use Module\System\Controller\Front\LoginController as ActionController;

/**
 * Login/logout for admin
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoginController extends ActionController
{
    /**
     * Grant access permission
     *
     * @return bool
     */
    public function permissionException()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function verifyResult(Result $result)
    {
        if (!$result->isValid()) {
            return $result;
        }
        $adminRoles = Pi::service('user')->getRole($result->getData('id'));
        if (!$adminRoles) {
            Pi::service('authentication')->clearIdentity();
            $result->setCode(-4)->setMessage(_a('Not privileged.'));
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
        $this->view()->setLayout('layout-simple');
    }

    /**
     * {@inheritDoc}
     */
    protected function getConfig($name = '')
    {
        if (!$this->configs) {
            $data = Pi::config('', '', 'admin');
            $config = array();
            array_walk($data, function ($value, $key) use (&$config) {
                // Remove prefix of `admin_`
                $key = substr($key, 6);
                $config[$key] = $value;
            });
            $loginDisable = Pi::config('admin_disable');
            if (null !== $loginDisable) {
                $config['login_disable'] = $loginDisable;
            }
            $this->configs = $config;
        }

        $result = $this->configs;
        if ($name) {
            $result = isset($result[$name]) ? $result[$name] : null;
        }

        return $result;
    }
}
