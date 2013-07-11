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
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

class User extends AbstractResource
{
    /**
     * @return void
     */
    public function boot()
    {
        $identity = (string) Pi::service('authentication')->getIdentity();
        Pi::service('user')->bind($identity, 'identity');
        Pi::registry('user', Pi::service('user')->getUser());
    }
}
