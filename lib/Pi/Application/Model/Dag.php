<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
     * {@inheritDoc}
     */
    protected $primaryKeyColumn = 'id';

    /**
     * {@inheritDoc}
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
