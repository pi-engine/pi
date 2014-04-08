<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
