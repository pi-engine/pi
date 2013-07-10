<?php
/**
 * Pi Engine user relation handler
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
 * Relation APIs:
 *   - relation([$id])->get($relation, $limit[, $offset[, $condition[, $order]]])   // Get IDs with relationship: friend, follower, following
 *   - relation([$id])->getCount($relation[, $condition]])                          // Get count with relationship: friend, follower, following
 *   - relation([$id])->hasRelation($uid, $relation)                                // Check if $id has relation with $uid: friend, follower, following
 *   - relation([$id])->add($uid, $relation)                                        // Add $uid as a relation: friend, follower, following
 *   - relation([$id])->delete([$uid[, $relation]])                                 // Delete $uid as relation: friend, follower, following
 */
class Relation extends AbstractHandler
{
    protected $isAvailable = null;

    /**
     * Check if relation function available
     *
     * @return bool
     */
    protected function isAvailable()
    {
        if (null === $this->isAvailable) {
            $this->isAvailable = Pi::service('module')->isActive('relation');
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