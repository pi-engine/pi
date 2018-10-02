<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */
namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('notification', 'user')->cronUserWithoutPhoto($list);
 */

class Notification extends AbstractApi
{
    public function cronUserWithoutPhoto ($userWithoutPhoto)
    {
        // Set to customer
        $user = array(
            $userWithoutPhoto['email'] => $userWithoutPhoto['name'],
        );

        Pi::service('notification')->send(
            $user,
            'cron-user-without-photo',
            array('username' => $userWithoutPhoto['name']),
            'user'
        );
        
    }
}
