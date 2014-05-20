<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model;

use Pi\Db\Table\AbstractTableGateway;

/**
 * Pi table gateway model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Model extends AbstractTableGateway
{
    /**
     * {@inheritDoc}
     */
    protected $primaryKeyColumn = 'id';

    /**
     * {@inheritDoc}
     */
    protected $rowClass = 'Pi\Db\RowGateway\RowGateway';
}
