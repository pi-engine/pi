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
        $id = Pi::api('message', 'message')->send(
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
        $id = Pi::api('message', 'message')->notify(
            $uid,
            $message,
            $subject,
            $tag
        );

        return $id;
    }

    /**
     * Get total account
     *
     * @param int $uid
     * @return int|bool
     */
    public function getCount($uid)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('message', 'message')->getCount($uid);

        return $result;
    }

    /**
     * Get new message account to alert
     *
     * @param int $uid
     * @return int|bool
     */
    public function getAlert($uid)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('message', 'message')->getAlert($uid);

        return $result;
    }
}
