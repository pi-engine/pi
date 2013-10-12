<?php
    /**
     * Pi Engine (http://pialog.org)
     *
     * @link            http://code.pialog.org for the Pi Engine source repository
     * @copyright       Copyright (c) Pi Engine http://pialog.org
     * @license         http://pialog.org/license.txt New BSD License
     */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User module nav api
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Nav extends AbstractApi
{
    protected $module = 'user';

    public function getList($cur, $uid = '')
    {
        $result = array(
            'cur'   => $cur,
            'items' => array()
        );

        $route = 'user';

        // Set homepage
        $params = array(
            'controller' => 'home',
            'action'     => 'index',
        );
        if ($uid) {
            $params['uid']    = $uid;
        }
        $url = Pi::service('url')->assemble($route, $params);
        $result['items'][] = array(
            'title' => __('Homepage'),
            'name'  => 'homepage',
            'url'   => $url,
            'icon'  => '',
        );

        // Set profile
        $params = array(
            'controller' => 'profile',
            'action'     => 'index',
        );
        if ($uid) {
            $params['uid'] = $uid;
        }
        $url = Pi::service('url')->assemble($route, $params);
        $result['items'][] = array(
            'title' => __('Profile'),
            'name'  => 'profile',
            'url'   => $url,
            'icon'  => '',
        );

        // Set activity
        $activityList = Pi::api('user', 'activity')->getList();
        foreach ($activityList as $key => $value) {
            $params = array(
                'controller' => 'activity',
                'action'     => 'index',
                'name'       => $key,
            );
            if ($uid) {
                $params['uid'] = $uid;
            }
            $url = Pi::service('url')->assemble($route, $params);

            $result['items'][] = array(
                'title' => $value['title'],
                'name'  => $key,
                'icon'  => $value['icon'],
                'url'   => $url,
            );
        }

        return $result;

    }
}