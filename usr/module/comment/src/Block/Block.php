<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Block;

use Pi;

class Block
{
    /**
     * Recent comments block
     */
    public static function post($options = array(), $module = null)
    {
        // Set options
        $block = array();
        $block = array_merge($block, $options);
        // Set options
        $limit = intval($block['limit']);
        $where = array(
        	'active' => 1
        );
        // Get posts list
        $posts = Pi::api('api', 'comment')->getList(
            $where,
            $limit
        );
        // Set render options
        $renderOptions = array(
            'user'      => array(
                'avatar'    => 'medium',
            ),
        );
        // Get render posts list
        $block['posts'] = Pi::api('api', 'comment')->renderList($posts, $renderOptions);
        // return
        return $block;
    }

    /**
     * Commented articles block
     */
    public static function article($options = array(), $module = null)
    {
        // Set options
        $block = array();
        $block = array_merge($block, $options);
        // Set options
        $limit = intval($block['limit']);
        // Top count
        $rowset = Pi::model('post', 'comment')->count(
            array('active' => 1),
            array('group' => 'root', 'limit' => $limit)
        );
        $roots = array();
        foreach ($rowset as $row) {
            $roots[$row['root']] = (int) $row['count'];
        }
        $rootIds = array_keys($roots);
        $targets = Pi::api('api', 'comment')->getTargetsByRoot($rootIds);
        array_walk($targets, function (&$target, $rootId) use ($roots) {
            $target['count'] = $roots[$rootId];
        });
        $block['targets'] = $targets;
        // return
        return $block;
    }

    /**
     * Top posters block
     */
    public static function user($options = array(), $module = null)
    {
        // Set options
        $block = array();
        $block = array_merge($block, $options);
        // Set options
        $limit = intval($block['limit']);
        // Top users
        $rowset = Pi::model('post', 'comment')->count(
            array('active' => 1),
            array('group' => 'uid', 'limit' => $limit)
        );
        $block['users'] = array();
        foreach ($rowset as $row) {
            $block['users'][$row['uid']] = array(
                'count' => (int) $row['count'],
            );
        }
        if ($block['users']) {
            $userNames = Pi::service('user')->mget(array_keys($block['users']), 'name');
            array_walk($block['users'], function (&$user, $uid) use ($userNames) {
                $user['name'] = $userNames[$uid];
                $user['profile'] = Pi::service('user')->getUrl('profile', $uid);
                $user['url'] = Pi::api('api', 'comment')->getUrl(
                    'user',
                    array('uid' => $uid)
                );
            });
        }
        // return
        return $block;
    }
}    