<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Hybridauth\Provider\Facebook as HybridauthFacebook;
use Hybridauth\Provider\GitHub as HybridauthGitHub;
use Hybridauth\Provider\Google as HybridauthGoogle;
use Hybridauth\Provider\Twitter as HybridauthTwitter;
use Pi;
use Pi\Authentication\Result;
use Pi\Mvc\Controller\ActionController;
use Zend\Math\Rand;

/**
 * OAuth controller
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class OauthController extends ActionController
{
    public function callbackAction()
    {
        // Check user
        if (Pi::service('user')->hasIdentity()) {
            $this->jump(['route' => 'home'], __('You logged in before.'));
        }

        // Get info from url
        $module   = $this->params('module');
        $provider = $this->params('provider');

        // Get config
        $config = Pi::service('registry')->config->read($module);

        // Set url array
        $url = Pi::api('oauth', 'user')->urlList();

        // Check provider
        switch ($provider) {
            case 'google':
                if (!empty($url['google'])
                    && $config['oauth_google']
                    && !empty($config['oauth_google_client_id'])
                    && !empty($config['oauth_google_client_secret'])
                ) {

                    // Set hybridauth config
                    $configHybridauth = [
                        'callback' => $url['google'],
                        'keys'     => [
                            'id'     => $config['oauth_google_client_id'],
                            'secret' => $config['oauth_google_client_secret'],
                        ],
                    ];

                    try {
                        //Instantiate adapter directly
                        $adapter = new HybridauthGoogle($configHybridauth);

                        //Attempt to authenticate the user with Facebook
                        $adapter->authenticate();

                        //Retrieve the user's profile
                        $userProfile = (array)$adapter->getUserProfile();

                        //Disconnect the adapter
                        $adapter->disconnect();

                    } catch (\Exception $e) {
                        echo 'Oops, we ran into an issue! ' . $e->getMessage();
                    }
                } else {
                    $this->jump(['route' => 'home'], __('Google login not active'));
                }
                break;

            case 'twitter':
                if (!empty($url['twitter'])
                    && $config['oauth_twitter']
                    && !empty($config['oauth_twitter_api_key'])
                    && !empty($config['oauth_twitter_api_secret'])
                ) {

                    // Set hybridauth config
                    $configHybridauth = [
                        'callback' => $url['twitter'],
                        'keys'     => [
                            'id'     => $config['oauth_twitter_api_key'],
                            'secret' => $config['oauth_twitter_api_secret'],
                        ],
                    ];

                    try {
                        //Instantiate adapter directly
                        $adapter = new HybridauthTwitter($configHybridauth);

                        //Attempt to authenticate the user with Facebook
                        $adapter->authenticate();

                        //Retrieve the user's profile
                        $userProfile = (array)$adapter->getUserProfile();

                        //Disconnect the adapter
                        $adapter->disconnect();

                    } catch (\Exception $e) {
                        echo 'Oops, we ran into an issue! ' . $e->getMessage();
                    }
                } else {
                    $this->jump(['route' => 'home'], __('Twitter login not active'));
                }
                break;

            case 'facebook':
                if (!empty($url['facebook'])
                    && $config['oauth_facebook']
                    && !empty($config['oauth_facebook_api_id'])
                    && !empty($config['oauth_facebook_api_secret'])
                ) {

                    // Set hybridauth config
                    $configHybridauth = [
                        'callback' => $url['facebook'],
                        'keys'     => [
                            'id'     => $config['oauth_facebook_api_id'],
                            'secret' => $config['oauth_facebook_api_secret'],
                        ],
                    ];

                    try {
                        //Instantiate adapter directly
                        $adapter = new HybridauthFacebook($configHybridauth);

                        //Attempt to authenticate the user with Facebook
                        $adapter->authenticate();

                        //Retrieve the user's profile
                        $userProfile = (array)$adapter->getUserProfile();

                        //Disconnect the adapter
                        $adapter->disconnect();

                    } catch (\Exception $e) {
                        echo 'Oops, we ran into an issue! ' . $e->getMessage();
                    }
                } else {
                    $this->jump(['route' => 'home'], __('Facebookr login not active'));
                }
                break;

            case 'github':
                if (!empty($url['github'])
                    && $config['oauth_github']
                    && !empty($config['oauth_github_client_id'])
                    && !empty($config['oauth_github_client_secret'])
                ) {

                    // Set hybridauth config
                    $configHybridauth = [
                        'callback' => $url['github'],
                        'keys'     => [
                            'id'     => $config['oauth_github_client_id'],
                            'secret' => $config['oauth_github_client_secret'],
                        ],
                    ];

                    try {
                        //Instantiate adapter directly
                        $adapter = new HybridauthGithub($configHybridauth);

                        //Attempt to authenticate the user with Facebook
                        $adapter->authenticate();

                        //Retrieve the user's profile
                        $userProfile = (array)$adapter->getUserProfile();

                        //Disconnect the adapter
                        $adapter->disconnect();

                    } catch (\Exception $e) {
                        echo 'Oops, we ran into an issue! ' . $e->getMessage();
                    }
                } else {
                    $this->jump(['route' => 'home'], __('Github login not active'));
                }
                break;


            default:
            case '':
                $this->jump(['route' => 'home'], __('Provider not set'));
                break;
        }

        if (isset($userProfile)
            && isset($userProfile['email'])
            && !empty($userProfile['email'])
        ) {
            // Check user
            $userAccount = Pi::model('user_account')->find($userProfile['email'], 'email');

            // Add user if not exist
            if (!$userAccount && !$config['register_disable']) {

                // Add user
                $user = [
                    'first_name'    => $userProfile['firstName'],
                    'last_name'     => $userProfile['lastName'],
                    'email'         => $userProfile['email'],
                    'identity'      => $userProfile['email'],
                    'name'          => $userProfile['displayName'],
                    'last_modified' => time(),
                    'ip_register'   => Pi::user()->getIp(),
                    'credential'    => Rand::getString(16, 'abcdefghijklmnopqrstuvwxyz123456789', true),
                ];

                // Get user id
                $uid = Pi::api('user', 'user')->addUser($user);

                // Check user add or not
                if ($uid) {
                    // Set user role
                    Pi::api('user', 'user')->setRole($uid, 'member');
                    // Active user
                    $status = Pi::api('user', 'user')->activateUser($uid);
                    if ($status) {
                        // Target activate user event
                        Pi::service('event')->trigger('user_activate', $uid);
                    }
                }

                // ToDo : send notification email
            }

            // Set authentication
            Pi::service('authentication')->setStrategy('oAuth');
            $result = Pi::service('authentication')->authenticate($userProfile['email'], '', '');
            $result = $this->verifyResult($result);

            if (!$result->isValid()) {
                $this->jump(['route' => 'home'], __('Error on login'));
            } else {
                $configUser = Pi::user()->config();

                $uid = (int)$result->getData('id');
                try {
                    Pi::service('user')->bind($uid);
                } catch (\Exception $e) {
                    return;
                }

                Pi::service('session')->setUser($uid);

                $rememberMe = 0;
                if ($configUser['rememberme']) {
                    $rememberMe = $configUser['rememberme'] * 86400;
                    Pi::service('session')->manager()
                        ->rememberme($rememberMe);
                }

                if (isset($_SESSION['PI_LOGIN'])) {
                    unset($_SESSION['PI_LOGIN']);
                }

                // Trigger login event
                $args = [
                    'uid'           => $uid,
                    'remember_time' => $rememberMe,
                ];
                Pi::service('event')->trigger('user_login', $args);

                $this->jump(['route' => 'home'], __('You have logged in successfully.'));
            }

        } else {
            $this->jump(['route' => 'home'], __('Information not true'));
        }
    }

    /**
     * Filtering Result after authentication
     *
     * @param Result $result
     *
     * @return Result
     */
    protected function verifyResult(Result $result)
    {
        return $result;
    }
}