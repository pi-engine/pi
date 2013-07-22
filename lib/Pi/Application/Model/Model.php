<?php
/**
 * Pi Table Gateway Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model;

use Pi\Db\Table\AbstractTableGateway;

class Model extends AbstractTableGateway
{
    /**
     * Primary key, required for model
     *
     * @var string
     */
    protected $primaryKeyColumn = 'id';

    /**
     * Classname for row
     *
     * @var string
     */
    protected $rowClass = 'Pi\\Db\\RowGateway\\RowGateway';
}
