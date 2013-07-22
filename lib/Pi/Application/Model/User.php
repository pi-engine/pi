<?php
/**
 * Pi User Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model;

use Pi;

class User extends Model
{
    /** @var string */
    protected $table = 'user_account';

    /**
     * Classname for row
     *
     * @var string
     */
    protected $rowClass = 'Pi\\Application\\Model\\User\\RowGateway\\Account';
}
