<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model\RowGateway;

use Pi;
use Pi\Application\Model\User\RowGateway\Account as AccountRowGateway;

/**
 * User account profile row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Account extends AccountRowGateway
{
    /**
     * {@inheritDoc}
     */
    protected function getMetaList()
    {
        return Pi::registry('profile', 'user')->read('account');
    }
}
