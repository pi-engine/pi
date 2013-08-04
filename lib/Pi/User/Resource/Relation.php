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
 * User relation handler
 *
 * Relation APIs:
 *
 *   - relation([$id])->get($relation, $limit, $offset, $condition, $order)
 *   - relation([$id])->getCount($relation[, $condition]])
 *   - relation([$id])->hasRelation($uid, $relation)
 *   - relation([$id])->add($uid, $relation)
 *   - relation([$id])->delete([$uid[, $relation]])                   
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Relation extends AbstractResource
{
    /**
     * If relation module available
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
