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
use Pi\Db\RowGateway\RowGateway;

/**
 * User profile row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends AbstractFieldRowGateway
{
    /** @var string Model type */
    protected static $type = 'profile';
}
