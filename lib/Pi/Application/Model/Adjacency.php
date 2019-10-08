<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model;

use Pi\Db\Table\AbstractAdjacency;

/**
 * Pi Adjacency List Table Gateway Model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Adjacency extends AbstractAdjacency
{
    /**
     * {@inheritDoc}
     */
    protected $primaryKeyColumn = 'id';

    /**
     * {@inheritDoc}
     */
    protected $column
        = [
            'parent' => 'parent',
        ];
}
