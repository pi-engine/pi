<?php
/**
 * User profile controller
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

/**
 * Feature list:
 * 1. User profile view
 */
class ProfileController extends ActionController
{
    public function indexAction()
    {
        $id = $this->params('id');
        if (!$id) {
            $this->redirect()->toRoute('user');
            return;
        }
        if (is_numeric($id)) {
            $row = Pi::model('user')->find($id);
        } else {
            $row = Pi::model('user')->find($id, 'identity');
        }
        $role = Pi::model('user_role')->find($row->id, 'user')->role;
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
