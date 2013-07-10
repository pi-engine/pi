<?php
/**
 * Pi Engine user message handler
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\User
 */

namespace Pi\User\Handler;

use Pi;

/**
 * Message APIs:
 *   - message([$id])->send($message, $from)                                        // Send message to a user
 *   - message([$id])->notify($message, $subject[, $tag])                           // Send notification to a user
 *   - message([$id])->getCount()                                                   // Get message total count of current user
 *   - message([$id])->getAlert()                                                   // Get message alert (new) count of current user
 */
class Message extends AbstractHandler
{
    protected $isAvailable = null;

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
     * @param string $message
     * @param int $from
     * @return int|false
     */
    public function send($message, $from)
    {
        if (!$this->isAvailable) {
            return false;
        }
        $id = Pi::service('api')->message->send($this->model->id, $message, $from);
        return $id;
    }

    /**
     * Send a notification
     *
     * @param string $message
     * @param string $subject
     * @param string $tag
     * @return int|false
     */
    public function notify($message, $subject, $tag = '')
    {
        if (!$this->isAvailable) {
            return false;
        }
        $id = Pi::service('api')->message->notify($this->model->id, $message, $subject, $tag);
        return $id;
    }

    /**
     * Get total account
     *
     * @return int|false
     */
    public function getAccount()
    {
        if (!$this->isAvailable) {
            return false;
        }
        $result = Pi::service('api')->message->getAccount($this->model->id);
        return $result;
    }

    /**
     * Get new message account to alert
     *
     * @return int|false
     */
    public function getAlert()
    {
        if (!$this->isAvailable) {
            return false;
        }
        $result = Pi::service('api')->message->getAlert($this->model->id);
        return $result;
    }
}