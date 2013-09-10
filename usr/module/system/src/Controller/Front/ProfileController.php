<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * User profile controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ProfileController extends ActionController
{
    /**
     * Profile data of mine
     *
     * @return void
     */
    public function indexAction()
    {
        $id = Pi::user()->getIdentity();
        if (!$id) {
            $this->jump(
                array('controller' => 'login'),
                __('Please log in.'),
                5
            );
            return;
        }
        $role = Pi::model('user_role')->find($id, 'uid')->role;
        $roleRow = Pi::model('acl_role')->find($role, 'name');
        $userRow = Pi::user()->getUser();
        $user = array(
            __('ID')        => $userRow->id,
            __('Identity')  => $userRow->identity,
            __('Email')     => $userRow->email,
            __('Name')      => $userRow->name,
            __('Role')      => __($roleRow->title),
        );

        $title = __('User profile');
        $this->view()->assign(array(
            'title' => $title,
            'user'  => $user,
        ));
        $this->view()->setTemplate('profile');
    }

    /**
     * Profile data of specified user
     *
     * @return void
     */
    public function viewAction()
    {
        $id = $this->params('uid', 0);
        $identity = $this->params('identity', '');
        $name = $this->params('name', '');
        if (!$id && $identity && $name) {
            $this->jump(
                array('action' => 'index'),
                __('The user does not exists.'),
                5
            );
            return;
        }
        if ($id) {
            $row = Pi::model('user_account')->find($id);
        } elseif ($identity) {
            $row = Pi::model('user_account')->find($identity, 'identity');
        } elseif ($name) {
            $row = Pi::model('user_account')->find($name, 'name');
        }
        if (!$row) {
            $this->jump(
                array('action' => 'index'),
                __('The user does not exists.'),
                5
            );
            return;
        }
        $role = Pi::model('user_role')->find($row->id, 'uid')->role;
        $roleRow = Pi::model('acl_role')->find($role, 'name');
        $user = array(
            __('ID')        => $row->id,
            __('Identity')  => $row->identity,
            __('Email')     => $row->email,
            __('Name')      => $row->name,
            __('Role')      => __($roleRow->title),
        );

        $title = __('User profile');
        $this->view()->assign(array(
            'title' => $title,
            'user'  => $user,
        ));
        $this->view()->setTemplate('profile');
    }
}
