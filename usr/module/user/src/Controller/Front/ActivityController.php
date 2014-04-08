<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Activity controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ActivityController extends ActionController
{
    /**
     * Display activity
     *
     * @return array|void
     */
    public function indexAction()
    {
        $name       = _get('name');
        $uid        = _get('uid');
        $ownerUid   = Pi::user()->getId();
        $limit      = Pi::config('list_limit', 'user');
        //$isOwner    = 0;

        if (!$uid && !$ownerUid) {
            $this->jump(
                array(
                    'controller' => 'profile',
                    'action'     => 'index'
                ),
                __('User was not found.'),
                'error'
            );
        }

        // Check is owner
        if (!$uid) {
            //$isOwner = 1;
            $uid     = $ownerUid;
        }
        if (!$name) {
            $this->jump(
                array(
                    'controller' => 'profile',
                    'action'     => 'index'
                ),
                __('An error occurred.'),
                'error'
            );
        }

        /*
        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('name', 'gender', 'birthdate'),
            true,
            true
        );
        if (!$user) {
            $this->jump(
                array(
                    'controller' => 'profile',
                    'action'     => 'index'
                ),
                __('User was not found.'),
                'error'
            );
        }

        // Get viewer role: public member follower following owner
        if ($isOwner) {
            $role = 'owner';
        } else {
            $role = Pi::user()->hasIdentity() ? 'member' : 'public';
        }
        $user = Pi::api('privacy', 'user')->filterProfile(
            $uid,
            $role,
            $user,
            'user'
        );
        */

        // Get activity list for nav display
        $activityList = Pi::api('activity', 'user')->getList();

        // Get current activity data
        $data = Pi::api('activity', 'user')->get($uid, $name, $limit);

        /*
        // Get nav
        if ($isOwner) {
            $nav = Pi::api('nav', 'user')->getList($name);
        } else {
            $nav = Pi::api('nav', 'user')->getList($name, $uid);
        }
        */

        // Get quick link
        //$quicklink = Pi::api('quicklink', 'user')->getList();

        $this->view()->assign(array(
            'list'      => $activityList,
            'name'      => $name,
            'data'      => $data,
            //'user'      => $user,
            //'nav'       => $nav,
            'uid'       => $uid,
            //'quicklink' => $quicklink,
            //'owner'     => $isOwner,
        ));

    }

    /**
     * Test for activity more link contents
     */
    public function moreAction()
    {
        $this->view()->setTemplate('activity-more');
    }
}
