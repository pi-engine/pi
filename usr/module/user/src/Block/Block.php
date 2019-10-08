<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\User\Block;

use Pi;

class Block
{
    public static function completeness($options = [], $module = null)
    {
        // Set options
        $block = [];
        $block = array_merge($block, $options);
        // Check uer
        if (Pi::service('authentication')->hasIdentity()) {
            // Get user id
            $uid = Pi::user()->getId();
            // Set fields list
            $block['fields'] = Pi::registry('display_field', 'user')->read();
            $block['fields'] = array_merge($block['fields'], ['avatar']);
            // Get met
            $block['meta'] = Pi::api('user', 'user')->getMeta('', 'display');
            // Get user profile
            $block['user'] = Pi::user()->get($uid, $block['fields']);
            // Set count
            $block['count']            = count($block['fields']);
            $block['countComplete']    = 0;
            $block['countNotComplete'] = 0;
            foreach ($block['user'] as $key => $userField) {
                if (in_array($key, $block['fields'])) {
                    if (empty($userField)) {
                        $block['countNotComplete'] = $block['countNotComplete'] + 1;
                    } else {
                        $block['countComplete'] = $block['countComplete'] + 1;
                    }
                }
            }
            $block['percent'] = ($block['countComplete'] * 100) / $block['count'];
            $block['percent'] = intval($block['percent']);
            // Check max percent
            if ($block['percent'] > $block['max_percent']) {
                return false;
            }
            // Check main fields
            $block['mainFields'] = [
                'avatar' => [
                    'name'   => 'avatar',
                    'title'  => _b('Avatar'),
                    'status' => empty($block['user']['avatar']) ? 0 : 1,
                ],
            ];
            foreach ($block['meta'] as $meta) {
                if ($meta['is_required'] && in_array($meta['name'], $block['fields'])) {
                    $block['mainFields'][$meta['name']] = [
                        'name'   => $meta['name'],
                        'title'  => $meta['title'],
                        'status' => empty($block['user'][$meta['name']]) ? 0 : 1,
                    ];
                }
            }
            // Set main fields status count
            $statusCount = 0;
            foreach ($block['mainFields'] as $mainFields) {
                if (!$mainFields['status']) {
                    $statusCount = $statusCount + 1;
                }
            }
            // Check main fields status count
            if (!$statusCount) {
                return false;
            }
            // Set url
            $block['accountUrl'] = Pi::url(Pi::service('user')->getUrl('user', ['controller' => 'account']));
            $block['avatarUrl']  = Pi::url(Pi::service('user')->getUrl('user', ['controller' => 'avatar']));
            $block['profileUrl'] = Pi::url(Pi::service('user')->getUrl('profile'));
            $block['avatar']     = Pi::service('user')->avatar($uid, 'large', [
                'alt'   => $block['user']['name'],
                'class' => 'rounded-circle',
            ]);
        } else {
            return false;
        }
        return $block;
    }
}