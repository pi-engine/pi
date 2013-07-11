<?php
/**
 * Pi Engine user external handler
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
 * @package         Pi\User
 */

namespace Pi\User\Handler;

use Pi;
use Pi\User\Model\AbstractModel as UserModel;
use Pi\User\BindInterface;

class AbstractHandler implements BindInterface
{
    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /**
     * Bind a user
     *
     * @param UserModel $user
     * @return AbstractHandler
     */
    public function bind(UserModel $model = null)
    {
        $this->model = $model;
        return $this;
    }
}