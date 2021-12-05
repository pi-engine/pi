<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Dashboard controller
 *
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */
class DashboardController extends ActionController
{
    /**
     * Edit base user information
     *
     * @return array|void
     */
    public function indexAction()
    {
        // Get config
        $config = Pi::service('registry')->config->read('user');

        // Check dashboard active
        if (!$config['dashboard_enable']) {
            return $this->jump([
                'controller' => 'profile',
                'action'     => 'index',
            ]);
        }

        // Check login in
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();

        // Get identity, email, name
        $user        = Pi::api('user', 'user')->get(
            $uid,
            ['identity', 'email', 'name']
        );
        $user['uid'] = $uid;
        $user['id']  = $uid;

        $this->view()->assign([
            'user' => $user,
        ]);

        $this->view()->headTitle(__('My Personal Dashboard'));
        $this->view()->headdescription(__('Basic settings'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }
}
