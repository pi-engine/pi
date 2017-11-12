<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        return Pi::registry('field', 'user')->read('account');
    }
}
