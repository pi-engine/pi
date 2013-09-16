<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;


/**
 * User manage cases controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class MaintenanceController extends ActionController
{
    /**
     * Default action
     *
     * @return array|\Zend\Mvc\Controller\Plugin\Redirect
     */
    public function indexAction()
    {
        return $this->redirect(
            '',
            array(
                'controller' => 'maintenance',
                'action' => 'log.list',
            )
        );
    }

    /**
     * User log
     */
    public function logAction()
    {
        $uid = _get('uid');
        $uid = 4;
        if (!$uid) {
            return $this->jumpTo404('Invalid uid');
        }

        // Check user exist
        $isExist = Pi::api('user', 'user')->getUser($uid)->id;
        if (!$isExist) {
            return $this->jumpTo404('Invalid uid');
        }

        // Get user basic information and user data

        $user = Pi::api('user', 'user')->get(
            $uid,
            array(
                'identity',
                'name',
                'id',
                'active',
                'time_disabled',
                'time_activated',
                'time_created',
                'ip_register',
            )
        );
        // Get user data
        $user['time_last_login'] = Pi::user()->data()->get($uid, 'time_last_login');
        $user['ip_login']    = Pi::user()->data()->get($uid, 'ip_login');
        $user['login_times'] = Pi::user()->data()->get($uid, 'login_times');

        $this->view()->assign(array());
    }

    /**
     * User log list
     */
    public function logListAction()
    {

    }

    /**
     * Deleted user list
     */
    public function deletedListAction()
    {

    }

    /**
     * Clear user
     */
    public function clearAction()
    {

    }
}