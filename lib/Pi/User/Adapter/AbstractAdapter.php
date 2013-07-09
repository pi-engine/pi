<?php
/**
 * Pi Engine user service abstract
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

namespace Pi\User\Adapter;

use Pi;
use Pi\User\Model\AbstractModel as UserModel;
use Pi\User\Avatar\Factory as UserAvatar;

abstract class AbstractAdapter
{
    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /**
     * Bind a user to service
     *
     * @param UserModel $user
     * @return UserModel
     */
    public function bind(UserModel $user = null)
    {
        $this->model = $user;
        return $this->model;
    }

    /**
     * Get user data object
     *
     * @param int|string|null   $identity   User id, identity or data object
     * @param string            $field      Field of the identity: id, identity, object
     * @return UserModel
     */
    abstract public function getUser($identity = null, $field = 'id');

    /**
     * Get account variables
     *
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        $result = null;
        if ($this->model) {
            $result = $this->model->$var;
        }

        return $result;
    }

    /**#@+
     * Account APIs
     */
    /**
     * Get user profile URL
     *
     * @param int $id
     * @return string
     */
    abstract public function getProfileUrl($id = null);

    /**
     * Get user login URL
     *
     * @return string
     */
    abstract public function getLoginUrl();

    /**
     * Get user logout URL
     *
     * @return string
     */
    abstract public function getLogoutUrl();

    /**
     * Get user register URL
     *
     * @return string
     */
    abstract public function getRegisterUrl();
    /**#@-*/

    /**#@+
     * Profile APIs
     */
    /**
     * Get user full name
     *
     * @param int $id
     * @return string
     */
    abstract public function getName($id = null);

    /**
     * Get user avatar img element
     *
     * @param int|null          $id             User id
     * @param string            $size           Size of image to display, integer for width, string for named size: 'mini', 'xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge'
     * @param array|string|bool $attributes     Array for attributes of HTML img element of img, string for alt of img, false to return URL
     * @return string
     */
    public function getAvatar($id = null, $size = '', $attributes = array())
    {
        $factory = new UserAvatar($this->getUser($id));
        $avatar = $factory->getAvatar($size, $attributes);
        return $avatar;
    }

    /**
     * Get user avatar img element through Gravatar
     *
     * @param int|null          $id             User id
     * @param int               $size           Size of image to display
     * @param array|string|bool $attributes     Array for attributes of HTML img element of img, string for alt of img, false to return URL
     * @return string
     */
    public function getGravatar($id = null, $size = 80, $attributes = array())
    {
        $factory = new UserAvatar($this->getUser($id));
        $avatar = $factory->getGravatar($size, $attributes);
        return $avatar;
    }
    /**#@-*/

    /**
     * Method handler allows a shortcut
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        trigger_error(sprintf(__CLASS__ . '::%s is not defined yet.', $method), E_USER_NOTICE);
        return 'Not defined';
    }
}