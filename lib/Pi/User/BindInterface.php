<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
