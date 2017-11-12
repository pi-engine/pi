<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model;

use Pi\Db\Table\AbstractNest;

/**
 * Nest type of model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Nest extends AbstractNest
{
    /**
     * {@inheritDoc}
     */
    protected $primaryKeyColumn = 'id';

    /**
     * {@inheritDoc}
     */
    protected $column = array(
        'left'  => 'left',
        'right' => 'right',
        'depth' => 'depth',
    );
}
