<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Laminas\Math\Rand;

/*
 * Pi::api('mobile', 'user')->generate($length);
 * Pi::api('mobile', 'user')->send($params);
 * Pi::api('mobile', 'user')->password($params);
 */

class Mobile extends AbstractApi
{
    public function generate($length = 4)
    {
        return Rand::getString($length, '0123456789');
    }

    public function send($params)
    {
        // Generate code
        $code = $this->generate();

        // Set name if not set on register form
        if (!isset($params['name']) || empty($params['name'])) {
            if (isset($params['first_name']) || isset($params['last_name'])) {
                $params['name'] = $params['first_name'] . ' ' . $params['last_name'];
            } else {
                $params['name'] = $params['identity'];
            }
        }

        // Check custom
        if (file_exists(Pi::path('config/custom/user.register.php'))) {
            include Pi::path('config/custom/user.register.php');
        } else {
            // Set sms content
            $content = sprintf(__('Dear %s, Your verify mobile code is : %s - %s'), $params['name'], $code, Pi::config('sitename'));

            // Send sms
            Pi::service('notification')->smsToUser($content, $params['identity']);
        }

        return $code;
    }

    public function password($params)
    {
        // Generate password
        $credential = Pi::api('mobile', 'user')->generate(6);

        // Update user account data
        Pi::api('user', 'user')->updateAccount(
            $params['uid'],
            ['credential' => $credential]
        );

        // Check custom
        if (file_exists(Pi::path('config/custom/user.password.php'))) {
            include Pi::path('config/custom/user.password.php');
        } else {
            // Set sms content
            $content = sprintf(__('Dear %s, Your new password is : %s - %s'), $params['name'], $credential, Pi::config('sitename'));

            // Send sms
            Pi::service('notification')->smsToUser($content, $params['identity']);
        }

        return $credential;
    }
}
