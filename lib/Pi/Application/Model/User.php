<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model;

/**
 * User model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'user_account';

    /**
     * {@inheritDoc}
     */
    protected $rowClass = 'Pi\Application\Model\User\RowGateway\Account';
}