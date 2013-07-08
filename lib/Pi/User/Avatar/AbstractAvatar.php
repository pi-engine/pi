<?php
/**
 * Pi Engine abstract user avatar
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

namespace Pi\User\Avatar;

use Pi;
use Pi\User\Model\AbstractModel as UserModel;

abstract class AbstractAvatar
{
    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /**
     * Constructor
     *
     * @param UserModel $model
     */
    public function __construct(UserModel $model = null)
    {
        $this->model = $model;
    }

    /**
     * Get user avatar link
     *
     * @param string            $size           Size of image to display, integer for width, string for named size: 'mini', 'xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge'
     * @return string
     */
    abstract public function build($size = '');
}
