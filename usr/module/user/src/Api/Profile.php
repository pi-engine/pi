<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * User profile manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends AbstractApi
{
    // Rule file name
    const RULE_FILE = 'profile-complete-rule';
    
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /**
     * Check if user profile is complete
     *
     * @param int $uid
     *
     * @return bool
     */
    public function isComplete($uid = 0)
    {
        $result = true;
        $config = Pi::user()->config('require_profile_complete');
        if ($config) {
            $uid = $uid ?: Pi::service('user')->getId();
            $status = $this->requireVerify($uid);
            if ($status) {
                $level = Pi::api('user', $this->module)->get($uid, 'level');
                $result = $level ? true : false;
            }
        }

        return $result;
    }

    /**
     * Require user to complete profile to access a resource
     *
     * @param int           $uid
     * @param string|bool   $redirect
     *
     * @return void
     */
    public function requireComplete($uid = 0, $redirect = '')
    {
        if ($this->isComplete($uid)) {
            return;
        }

        if (false === $redirect) {
            $redirect = Pi::url('www');
        } elseif (!$redirect) {
            $redirect = Pi::service('url')->getRequestUri();
        }
        Pi::service('url')->redirect(
            Pi::service('url')->assemble(
                'user',
                array(
                    'controller' => 'register',
                    'action' => 'profile.complete',
                )
            ),
            $redirect
        );
    }
    
    /**
     * Read the profile-complete-rule file in custom folder to check whether
     * need this user to complete profile
     * 
     * @param int $uid
     * @return boolean
     */
    protected function requireVerify($uid = 0)
    {
        $result = true;
        
        // Read rule list
        $uid = $uid ?: Pi::service('user')->getId();
        $file = sprintf(
            '%s/module/%s/config/%s.php',
            Pi::path('custom'),
            $this->getModule(),
            self::RULE_FILE
        );
        if (!file_exists($file)) {
            $file = sprintf(
                '%s/%s/config/%s.php',
                Pi::path('module'),
                $this->getModule(),
                self::RULE_FILE
            );
            if (!file_exists($file)) {
                return false;
            }
        }
        $data = include $file;
        
        // Check if condition field is exists
        if (empty($data) || !isset($data['rule_field'])) {
            return false;
        }
        
        // Get condition value, and assemble key value
        if (empty($data['rule_field'])) {
            $key = 'all';
        } else {
            $fields = explode('&', $data['rule_field']);
            $values = Pi::api('user', $this->module)->get($uid, $fields);
            foreach ($values as $key => &$value) {
                if (!in_array($key, $fields)) {
                    unset($values[$key]);
                }
                $value = $value ?: 'default';
            }
            $key = implode('&', $values);
        }
        
        // Not verify if no rule list acquired
        if (
            !isset($data['items'])
            || !isset($data['items'][$key])
            || empty($data['items'][$key])
        ) {
            return false;
        }
        
        // Not verify if rule is deactivate
        if (isset($data['items'][$key]['active'])) {
            $result = (bool) $data['items'][$key]['active'];
        }
        
        return $result;
    }
}
