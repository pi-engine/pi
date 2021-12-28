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
 * User OAuth APIs
 *
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */

/**
 * Pi::api('oauth', 'user')->urlList();
 */
class Oauth extends AbstractApi
{
    public function urlList()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->module);

        // Set url array
        $url = [];

        // Check user
        if (!$config['oauth_login']) {
            return $url;
        }

        // Set google
        $url['google'] = '';
        if ($config['oauth_google']
            && !empty($config['oauth_google_client_id'])
            && !empty($config['oauth_google_client_secret'])
        ) {
            $url['google'] = Pi::url(Pi::service('url')->assemble('user', [
                'module'     => 'user',
                'controller' => 'oauth',
                'action'     => 'callback',
                'provider'   => 'google',
            ]));
        }

        // Set twitter
        $url['twitter'] = '';
        if ($config['oauth_twitter']
            && !empty($config['oauth_twitter_api_key'])
            && !empty($config['oauth_twitter_api_secret'])
        ) {
            $url['twitter'] = Pi::url(Pi::service('url')->assemble('user', [
                'module'     => 'user',
                'controller' => 'oauth',
                'action'     => 'callback',
                'provider'   => 'twitter',
            ]));
        }

        // Set github
        $url['github'] = '';
        if ($config['oauth_github']
            && !empty($config['oauth_github_client_id'])
            && !empty($config['oauth_github_client_secret'])
        ) {
            $url['github'] = Pi::url(Pi::service('url')->assemble('user', [
                'module'     => 'user',
                'controller' => 'oauth',
                'action'     => 'callback',
                'provider'   => 'github',
            ]));
        }

        // Set facebook
        $url['facebook'] = '';
        if ($config['oauth_facebook']
            && !empty($config['oauth_facebook_api_id'])
            && !empty($config['oauth_facebook_api_secret'])
        ) {
            $url['facebook'] = Pi::url(Pi::service('url')->assemble('user', [
                'module'     => 'user',
                'controller' => 'oauth',
                'action'     => 'callback',
                'provider'   => 'facebook',
            ]));
        }

        // return url list
        return $url;
    }
}
