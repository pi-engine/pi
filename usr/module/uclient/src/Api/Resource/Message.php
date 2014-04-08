<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Uclient\Api\Resource;

use Pi;
use Pi\User\Resource\Message as UserMessage;

/**
 * Message handler
 *
 * Message APIs:
 *
 *   - send($uid, $message, $from)
 *   - notify($uid, $message, $subject, $tag)
 *   - getCount($uid)
 *   - getAlert($uid)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Message extends UserMessage
{
    /**
     * If user module available for time handling
     * @var bool|null
     */
    protected $isAvailable = true;

    /**
     * Check if message function available
     *
     * @return bool
     */
    protected function isAvailable()
    {
        return true;
    }

    /**
     * Send a message
     *
     * @param int       $uid
     * @param string    $message
     * @param int       $from
     *
     * @return int|bool
     */
    public function send($uid, $message, $from)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $time = time();
        $params = compact('uid', 'message', 'from', 'time');
        $params['app_key'] = $this->options['app_key'];
        $uri = $this->options['api']['send'];
        $result = Pi::service('remote')
            ->setAuthorization($this->options['authorization'])
            ->post($uri, $params);
        $result = (bool) $result['status'];

        return $result;
    }

    /**
     * Send a notification
     *
     * @param int       $uid
     * @param string    $message
     * @param string    $subject
     * @param string    $tag
     *
     * @return int|bool
     */
    public function notify($uid, $message, $subject, $tag = '')
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $time = time();
        $params = compact('uid', 'message', 'subject', 'time', 'tag');
        $params['app_key'] = $this->options['app_key'];
        $uri = $this->options['api']['notify'];
        $result = Pi::service('remote')
            ->setAuthorization($this->options['authorization'])
            ->post($uri, $params);
        $result = (bool) $result['status'];

        return $result;
    }

    /**
     * Get total count
     *
     * @param int $uid
     * @return int|bool
     */
    public function getCount($uid)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $params = compact('uid');
        $params['app_key'] = $this->options['app_key'];
        $uri = $this->options['api']['count'];
        $result = Pi::service('remote')
            ->setAuthorization($this->options['authorization'])
            ->get($uri, $params);
        $result = (bool) $result['status'];

        return $result;
    }

    /**
     * Get new message count to alert
     *
     * Alert user the new messages he receives since last visit.
     *
     * @param int $uid
     * @return int|bool
     */
    public function getAlert($uid)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('api', 'message')->getAlert($uid);

        return $result;
    }

    /**
     * Dismiss message alert by resetting alert count to 0
     *
     * @param $uid
     *
     * @return bool
     */
    public function dismissAlert($uid)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('api', 'message')->dismissAlert($uid);

        return $result;
    }

    /**
     * Get link to message service
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->isAvailable()) {
            return '';
        }
        $url = $this->options['link'];

        return $url;
    }
}
