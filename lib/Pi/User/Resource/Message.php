<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Resource;

use Pi;

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
class Message extends AbstractResource
{
    /**
     * Check if message function available
     *
     * @return bool
     */
    protected function isAvailable()
    {
        if (null === $this->isAvailable) {
            $this->isAvailable = Pi::service('module')->isActive('message');
        }

        return $this->isAvailable;
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
        $id = Pi::api('message')->send(
            $uid,
            $message,
            $from
        );

        return $id;
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
        $id = Pi::api('message')->notify(
            $uid,
            $message,
            $subject,
            $tag
        );

        return $id;
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
        $result = Pi::api('message')->getCount($uid);

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
        $result = Pi::api('message')->getAlert($uid);

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
        $result = Pi::api('message')->dismissAlert($uid);

        return $result;
    }
}
