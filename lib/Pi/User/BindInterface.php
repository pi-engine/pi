<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User;

use Pi;
use Pi\User\Model\AbstractModel as UserModel;

/**
 * Pi Engine user bind interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
interface BindInterface
{
    /**
     * Bind a user to service
     *
     * @param UserModel $user
     * @return UserModel
     */
    public function bind(UserModel $user = null);
}
