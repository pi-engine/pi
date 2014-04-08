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
use Pi\User\Resource\Relation as UserRelation;

/**
 * User relation handler
 *
 * Relation APIs:
 *
 *   - relation->get($uid, $relation, $limit, $offset, $condition, $order)
 *   - relation->getCount($uid, $relation[, $condition]])
 *   - relation->hasRelation($uid, $relation)
 *   - relation->add($uid, $relation)
 *   - relation->delete($uid, $relation)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Relation extends UserRelation
{
    /**
     * If user module available for time handling
     * @var bool|null
     */
    protected $isAvailable = true;

    /**
     * Placeholder for APIs
     *
     * @param string $method
     * @param array $args
     * @return bool|void
     */
    public function __call($method, $args)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }
}
