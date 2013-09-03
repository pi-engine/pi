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
class IndexController extends ActionController
{
    public function indexAction()
    {
        $page  = $this->params('p', 1);
        $limit = $this->params('limit', 20);
        $offset = (int) ($page -1) * $limit;

        // Get normal user ids list
        $ids = Pi::api('user', 'user')->getUids(
            array('active' => 1),
            $limit,
            $offset
        );

        // Get normal user count
        $count = Pi::api('user', 'user')->getCount(array('active' => 1));

        // Get user information
        $users = $this->getUser($ids, 'active');

        $this->view()->assign(array(
            'users' => $users
        ));
    }

    /**
     * Test user
     */
    public function testAction()
    {
        $this->view()->setTemplate(false);

        $model = $this->getModel('account');

        for ($uid = 7; $uid < 30; $uid++)
        {
            $row = $model->update(
                array(
                    'active' => 1,
                    'time_created' => time() - $uid * 3600 * 12,
                    'time_activated' => time() - $uid * 3600 * 6,
                ),
                array('id' => $uid)
            );
        }
    }

    /**
     * Get user information according to type
     * Type: active, pending search
     *
     * @param $ids
     * @param $type
     * @return array
     */
    protected function getUser($ids, $type)
    {
        $return = array();
        if (!$ids || !$type) {
            return $return;
        }

        if ($type == 'active') {
            $return = array(
                'identity'      => '',
                'name'          => '',
                'email'         => '',
                'time_disabled' => '',
                'front_role'    => '',
                'admin_role'    => '',
                'register_ip'   => '',
                'time_created'  => '',
                'login_time'    => '',
                'id'            => '',
            );

            $users = Pi::api('user', 'user')->get(
                $ids,
                array_keys($return)
            );

            foreach ($users as &$user) {
                $user = array_merge($return, $user);

                // Get role
                $user['front_role'] = Pi::api('user', 'user')->getRole(
                    $user['id']
                );
                $user['admin_role'] = Pi::api('user', 'user')->getRole(
                    $user['id'],
                    'admin'
                );

                // Get register ip
                // TO DO

                // Get login time
                // TO DO
            }
            $return = $users;
        }
        return $return;
    }
}