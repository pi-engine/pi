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
 * User home controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HomeController extends ActionController
{
    /**
     * Profile data
     *
     * @return void
     */
    public function indexAction()
    {
        $id = Pi::user()->getIdentity();
        if (!$id) {
            $this->redirect()->toRoute(
                '',
                array('controller' => 'login')
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
}
