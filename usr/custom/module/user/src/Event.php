<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Custom\User;

use Pi;
use Module\User\Event as BaseEvent;

/**
 * User custom Event Handler
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class Event extends BaseEvent
{
    /**
     * User join community event
     *
     * @param int $uid
     */
    public static function userActivate($uid)
    {
        // Get community id
        $commnuityId = Pi::api('user', 'user')->get($uid, 'registered_source') ?: 16;
        $uri    = 'http://www.eefocus.com/passport/api.php';
        $params = array(
            'act' => 'join',
            'uid' => $uid,
            'pid' => $commnuityId
        );
        Pi::service('remote')->get($uri, $params);
    }
}
