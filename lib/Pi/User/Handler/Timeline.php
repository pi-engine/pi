<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Handler;

use Pi;

/**
 * User timeline handler
 *
 * Timeline APIs:
 *   - timeline([$id])->get($limit[, $offset[, $condition]])                        // Get timeline list
 *   - timeline([$id])->getCount([$condition]])                                     // Get timeline count subject to condition
 *   - timeline([$id])->add($message, $module[, $tag[, $time]])                     // Add activity to user timeline
 *   - timeline([$id])->getActivity($name, $limit[, $offset[, $condition]])         // Get activity list of a user
 *   - timeline([$id])->delete([$condition])                                        // Delete timeline items subjecto to condition
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timeline extends AbstractHandler
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