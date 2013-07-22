<?php
/**
 * Pi User Account Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model\User;

use Pi;
use Pi\Application\Model\Model;

class Account extends Model
{
    /** @var string */
    protected $table = "user_account";

    /**
     * Row gateway class
     *
     * @var string
     */
    protected $rowClass = 'Pi\Application\Model\User\RowGateway\Account';

    /**
     * Get identity column
     *
     * @return string
     */
    public function getIdentityColumn()
    {
        return 'identity';
    }
}
