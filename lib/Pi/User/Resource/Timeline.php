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
 * User timeline handler
 *
 * Timeline APIs:
 *
 *   - timeline([$id])->get($limit[, $offset[, $condition]])
 *   - timeline([$id])->getCount([$condition]])
 *   - timeline([$id])->add($message, $module[, $tag[, $time]])
 *   - timeline([$id])->getActivity($name, $limit[, $offset[, $condition]])
 *   - timeline([$id])->delete([$condition])
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timeline extends AbstractResource
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
     * Placeholder for APIs
     *
     * @param string $method
     * @param array $args
     * @return bool|void
     */
    public function __call($method, $args)
    {
        if (!$this->isAvailable) {
            return false;
        }
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }
}
