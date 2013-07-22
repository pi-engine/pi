<?php
/**
 * Pi User Profile Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model\User;

use Pi;
use Pi\Application\Model\Model;

class Profile extends Model
{
    /**
     * Classname for row
     *
     * @var string
     */
    protected $rowClass = 'Pi\Model\User\RowGateway\Profile';
}
