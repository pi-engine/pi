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

class ActivityController extends ActionController
{
    /**
     * Display activity
     *
     * @return array|void
     */
    public function indexAction()
    {
        $name  = $this->params('name', '');
        $uid   = Pi::user()->getIdentity();
        $limit = 10;

        // Redirect login page if not logged in
        if (!$uid) {
            $this->jump(
                'user',
                array('controller' => 'login', 'action' => 'index'),
                __('Need login'),
                2
            );
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

        $this->view()->assign(array(
            'activity_list'     => $activityList,
            'activity_contents' => $activityContents,
            'cur_activity'      => $name,
            'user'             => $user,
            'is_owner'          => true,
        ));
    }
}
