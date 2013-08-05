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
     * Profile data
     *
     * @return void
     */
    public function indexAction()
    {
        $id = $this->params('id');
        if (!$id) {
            $this->redirect()->toRoute('user',
                array('controller' => 'account'));
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
