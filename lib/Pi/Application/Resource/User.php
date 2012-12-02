<?php
/**
 * Bootstrap resource
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Resource
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Resource;

use Pi;
use Pi\Application\User as UserModel;

class User extends AbstractResource
{
    public function boot()
    {
        if (Pi::registry('user')) {
            return Pi::registry('user');
        }
        $identity = Pi::service('authentication')->getIdentity();
        if ($identity) {
            $user = new UserModel($identity);
        } else {
            $user = new UserModel;
        }
        Pi::registry('user', $user);

        return $user;
    }
}
