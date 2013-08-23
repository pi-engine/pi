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
 *   - message([$id])->send($message, $from)
 *   - message([$id])->notify($message, $subject[, $tag])
 *   - message([$id])->getCount()
 *   - message([$id])->getAlert()
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Message extends AbstractResource
{
    /**
     * If message module available
     * @var bool|null
     */
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
        if (!$this->isAvailable()) {
            return false;
        }
        $id = Pi::api('message')->send(
            $this->model->id,
            $message,
            $from
        );

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
        if (!$this->isAvailable()) {
            return false;
        }
        $id = Pi::api('message')->notify(
            $this->model->id,
            $message,
            $subject,
            $tag
        );

        return $id;
    }

    /**
     * Get total account
     *
     * @return int|false
     */
    public function getAccount()
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('message')->getAccount($this->model->id);

        return $result;
    }

    /**
     * Get new message account to alert
     *
     * @return int|false
     */
    public function getAlert()
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('message')->getAlert($this->model->id);

        return $result;
    }
}
