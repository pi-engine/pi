<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    protected $column = array(
        'parent'    => 'parent'
    );
}
