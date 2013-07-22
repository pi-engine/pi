<?php
/**
 * Pi Config Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model;

class Config extends Model
{
    /**
     * Classname for row
     *
     * @var string
     */
    protected $rowClass = 'Pi\\Application\\Model\\RowGateway\\Config';
}
