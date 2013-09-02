<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\User;

use Pi\Application\Model\Model;

/**
 * User account model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Account extends Model
{
    /** @var string Table name */
    protected $table = 'user_account';

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
