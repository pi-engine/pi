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
use Pi\Paginator\Paginator;
use Pi\Acl\Acl;

/**
* User manage cases controller
*
* @author Liu Chuang <liuchuang@eefocus.com>
*/
class IndexController extends ActionController
{
    public function indexAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
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

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'index',
            'action'     => 'index',
        );
        $paginator = $this->setPaginator($paginatorOption);
        $this->view()->assign(array(
            'users'     => $users,
            'paginator' => $paginator,
            'page'      => $page,
            'curNav'    => 'active',
            'frontRole' => $this->getRoleSelectOptions(),
            'adminRole' => $this->getRoleSelectOptions('admin'),
        ));
    }

    /**
     * Enable users
     * @return array
     */
    public function enableAction()
    {
        $uids = _post('ids', '');
        if (!$uids) {
            return array(
                'status' => 0,
            );
        }

        $uids = explode(',', $uids);

        foreach ($uids as $uid) {
            $status = Pi::api('usr', 'user')->enable($uid);
            if (!$status) {
                break;
            }
        }

        return array(
            'status' => $status ? 1 : 0,
        );

        $this->view()->setTemplate(false);
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
                    $user['id'],
                    'front'
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

    /**
     * Get system all role for select form
     *
     * @param $section
     * @return array
     */
    protected function getRoleSelectOptions($section = 'front')
    {
        $model = Pi::model('acl_role');
        if ($section == 'front') {
            // Get front role
            $options = array(
                '' => __('Front role')
            );

            $rowset = $model->select(array('section' => 'front'));
            foreach ($rowset as $row) {
                $options[$row->name] = __($row->title);
            }
        }
        if ($section == 'admin') {
            // Get admin role
            $options = array(
                '' => __('Admin role'),
            );

            $rowset = $model->select(array('section' => 'admin'));
            foreach ($rowset as $row) {
                $options[$row->name] = __($row->title);
            }
        }

        return $options;

    }

    /**
     * Set paginator
     *
     * @param $option
     * @return \Pi\Paginator\Paginator
     */
    protected function setPaginator($option)
    {
        $paginator = Paginator::factory(intval($option['count']));
        $paginator->setItemCountPerPage($option['limit']);
        $paginator->setCurrentPageNumber($option['page']);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => $option['controller'],
                'action'        => $option['action'],
                'uid'           => $option['uid'],
            ),
        ));

        return $paginator;
    }
}