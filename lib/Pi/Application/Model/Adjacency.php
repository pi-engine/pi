<?php
/**
 * Pi Adjacency List Table Gateway Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model;

use Pi\Db\Table\AbstractAdjacency;

class Adjacency extends AbstractAdjacency
{
    /**
     * Primary key, required for model
     *
     * @var string
     */
    protected $primaryKeyColumn = 'id';

    /**
     * Predefined columns
     *
     * @var array
     */
    protected $column = array(
        'parent'    => 'parent'
    );
}
