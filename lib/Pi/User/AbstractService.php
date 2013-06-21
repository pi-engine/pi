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

namespace Pi\User;

abstract class AbstractService
{
    /**
     * Bound user identity
     * @var string
     */
    protected $identity;

    /**
     * Bind a user to service
     *
     * @param string $identity
     * @return AbstractService
     */
    public function bind($identity = null)
    {
        $this->identity = $identity;
        return $this;
    }

    /**
     * Get user full name
     *
     * @param string $identity
     * @return string
     */
    abstract public function getName($identity = null);

    /**
     * Get user profile URL
     *
     * @param string $identity
     * @return string
     */
    abstract public function getProfileUrl($identity = null);

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