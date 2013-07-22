<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model;

use Pi;

/**
 * User model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends Model
{
    /** @var string */
    protected $table = 'user_account';

    /**
     * Classname for row
     *
     * @var string
     */
    protected $rowClass = 'Pi\Application\Model\User\RowGateway\Account';
}
