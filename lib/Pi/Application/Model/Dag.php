<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model;

use Pi\Db\Table\AbstractDag;

/**
 * Pi DAG Table Gateway Model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Dag extends AbstractDag
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
        // Start vertex column name
        'start'     => 'start',
        // End vertex column name
        'end'       => 'end',
        // Entry edge column name
        'entry'     => 'entry',
        // Direct edge column name
        'direct'    => 'direct',
        // Exit edge column name
        'exit'      => 'exit',
        // Number of hops from start to end
        'hops'      => 'hops',
    );
}
