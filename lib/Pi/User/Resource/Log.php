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
 * User action log handler
 *
 * Log APIs:
 *
 * - log([$id])->add($action, $data[, $time])
 * - log([$id])->get($action, $limit[, $offset[, $condition]])
 * - log([$id])->getLast($action)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Log extends AbstractResource
{
    /**
     * If user module available for time handling
     * @var bool|null
     */
    protected $isAvailable = null;

    /**
     * Check if relation function available
     *
     * @return bool
     */
    protected function isAvailable()
    {
        if (null === $this->isAvailable) {
            $this->isAvailable = Pi::service('module')->isActive('user');
        }

        return $this->isAvailable;
    }

    /**
     * Write an action log
     *
     * @param string $action
     * @param string $data
     * @param int $time
     * @return int
     */
    public function add($action, $data = '', $time = null)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $row = Pi::model('log', 'user')->createRow(array(
            'uid'       => $this->model->id,
            'action'    => $acion,
            'data'      => $data,
            'time'      => $time ?: time(),
        ));
        $id = $row->save();

        return $id;
    }

    /**
     * Placeholder for APIs
     *
     * @param string $method
     * @param array $args
     * @return bool|void
     */
    public function __call($method, $args)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }
}
