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
        Pi::service('authentication')->requireLogin();

        $userRow = Pi::user()->getUser();
        $roles   = $userRow->role();
        $roleList = Pi::registry('role')->read();
        $userRole = array();
        foreach ($roles as $role) {
            $userRole[] = $roleList[$role]['title'];
        }
        $roleString = implode(' | ', $userRole);

        $pwUrl = $this->url('', array('controller' => 'password'));
        $pwString = sprintf(
            '<a href="%s" title="">%s</a>',
            $pwUrl,
            __('Change password')
        );

        $loUrl = Pi::service('authentication')->getUrl('logout');
        $loString = sprintf(
            '<a href="%s" title="">%s</a>',
            $loUrl,
            __('Logout')
        );
        $user = array(
            __('ID')        => $userRow->id,
            __('Username')  => $userRow->identity,
            __('Email')     => $userRow->email,
            __('Name')      => $userRow->name,
            __('Role')      => $roleString,
            __('Password')  => $pwString,
            __('Logout')    => $loString,
        );

        $avatar = Pi::service('avatar')->get($userRow->id);
        $title = __('User profile');
        $this->view()->assign(array(
            'title'     => $title,
            'user'      => $user,
            'avatar'    => $avatar,
        ));
        $this->view()->setTemplate('profile');
    }

    /**
     * Profile data of specified user
     *
     * Not used
     *
     * @return void
     */
    public function viewAction()
    {
        $this->redirect('', array('action' => 'index'));
    }
}
