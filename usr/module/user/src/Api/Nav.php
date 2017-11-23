<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * User module nav api
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Nav extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    public function getList($cur, $uid = 0)
    {
        $result = [
            'cur'   => $cur,
            'items' => [],
        ];

        $route = 'user';

        // Set profile
        $params = [
            'controller' => 'profile',
            'action'     => 'index',
        ];
        if ($uid) {
            $params['uid'] = $uid;
        }
        $url               = Pi::service('url')->assemble($route, $params);
        $result['items'][] = [
            'title' => __('Profile'),
            'name'  => 'profile',
            'url'   => $url,
            'icon'  => '',
        ];

        // Set homepage
        $params = [
            'controller' => 'home',
            'action'     => 'index',
        ];
        if ($uid) {
            $params['uid'] = $uid;
        }

        if (Pi::user()->getId()) {
            $url               = Pi::service('url')->assemble($route, $params);
            $result['items'][] = [
                'title' => __('Feed'),
                'name'  => 'homepage',
                'url'   => $url,
                'icon'  => '',
            ];
        }

        // Set activity
        $activityList = Pi::api('activity', 'user')->getList($uid);

        foreach ($activityList as $key => $value) {
            $params = [
                'controller' => 'activity',
                'action'     => 'index',
                'name'       => $key,
            ];
            if ($uid) {
                $params['uid'] = $uid;
            }
            $url = Pi::service('url')->assemble($route, $params);

            $result['items'][] = [
                'title' => $value['title'],
                'name'  => $key,
                'icon'  => $value['icon'],
                'url'   => $url,
                'count' => $value['count'],
            ];
        }

        return $result;
    }
}

;
