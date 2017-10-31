<?php
    /**
     * Pi Engine (http://pialog.org)
     *
     * @link            http://code.pialog.org for the Pi Engine source repository
     * @copyright       Copyright (c) Pi Engine http://pialog.org
     * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $result = array(
            'cur'   => $cur,
            'items' => array()
        );

        $route = 'user';

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

        // Set homepage
        $params = array(
            'controller' => 'home',
            'action'     => 'index',
        );
        if ($uid) {
            $params['uid'] = $uid;
        }
        
        if (Pi::user()->getId()) {
            $url = Pi::service('url')->assemble($route, $params);
            $result['items'][] = array(
                'title' => __('Feed'),
                'name'  => 'homepage',
                'url'   => $url,
                'icon'  => '',
            );
        }

        $params['action'] = 'comment';        
        $url = Pi::service('url')->assemble($route, $params);
        $result['items'][] = array(
            'title' => __('Reviews & Comments'),
            'name'  => 'comment',
            'url'   => $url,
            'icon'  => '',
            'count' => Pi::api('api', 'comment')->getCount(array('uid' => $uid ? $uid : Pi::user()->getId(), 'active' => 1, 'reply' => 0))
        );
        
        $params['action'] = 'item';
        $url = Pi::service('url')->assemble($route, $params);
        $owner = Pi::api('owner', 'guide')->getOwner($uid ? $uid : Pi::user()->getId(), 'uid');
        $result['items'][] = array(
            'title' => __('Items'),
            'name'  => 'item',
            'url'   => $url,
            'icon'  => '',
            'count' =>  Pi::api('item', 'guide')->getCountFromOwner($owner['id'], false)
        );
        
        $params['action'] = 'favorite';
        $url = Pi::service('url')->assemble($route, $params);
        $result['items'][] = array(
            'title' => __('Favorites'),
            'name'  => 'favorite',
            'url'   => $url,
            'icon'  => '',
            'count' =>  Pi::api('favourite', 'favourite')->getCount($uid ? $uid : Pi::user()->getId())
        );
        

        // Set activity
        $activityList = Pi::api('activity', 'user')->getList();
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
