<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Laminas\Math\Rand;

/*
 * Pi::api('mobile', 'user')->generate();
 * Pi::api('mobile', 'user')->send($params);
 * Pi::api('mobile', 'user')->verify($params);
 */

class Mobile extends AbstractApi
{
    public function generate()
    {
        return Rand::getString(4, '0123456789');
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

        // Set sms content
        $content = sprintf(__('Dear %s, Your verify mobile code is : %s - %s'), $params['name'], $code, Pi::config('sitename'));

        // Send sms
        Pi::service('notification')->smsToUser($content, $params['identity']);

        return $code;
    }

    public function verify($params)
    {

    }
}