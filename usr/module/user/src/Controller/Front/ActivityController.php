<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        $name     = _get('name');
        $uid      = _get('uid');
        $ownerUid = Pi::user()->getIdentity();
        $limit    = 10;
        $isOwner  = 0;

        if (!$uid && !$ownerUid) {
            return $this->jumpTo404('An error occur');
        }

        // Check is owner
        if (!$uid) {
            $isOwner = 1;
            $uid     = $ownerUid;
        }
        if (!$name) {
            $this->jumpTo404('An error occur');
        }

        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('name', 'gender', 'birthdate'),
            true
        );


        // Get activity list for nav display
        $activityList = Pi::api('user', 'activity')->getList();

        // Get activity contents
        $activityContents = Pi::api('user', 'activity')->get($uid, $name, $limit);

        // Get nav
        if ($isOwner) {
            $nav = Pi::api('user', 'nav')->getList($name);
        } else {
            $nav = Pi::api('user', 'nav')->getList($name, $uid);
        }

        $this->view()->assign(array(
            'activity_list'     => $activityList,
            'activity_contents' => $activityContents,
            'cur_activity'      => $name,
            'user'              => $user,
            'nav'               => $nav,
            'uid'               => $uid,
            'is_owner'          => $isOwner,
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
