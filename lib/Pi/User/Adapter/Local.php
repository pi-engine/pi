<?php
/**
 * Pi Engine local (or built-in) user service
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
use Pi\User\Model\Local as UserModel;

class Local extends AbstractAdapter
{
    /**
     * Get user data object
     *
     * @param int|string|null   $identity   User id, identity or data object
     * @param string            $field      Field of the identity: id, identity, object
     * @return UserModel
     */
    public function getUser($identity = null, $field = 'id')
    {
        if (null !== $identity) {
            $model = new UserModel($identity, $field);
        } else {
            $model = $this->model;
        }
        return $model;
    }

    /**#@+
     * Account APIs
     */
    /**
     * {@inheritDoc}
     */
    public function getProfileUrl($id = null)
    {
        $id = $id ?: $this->id;
        $url = Pi::service('url')->assemble('user', array(
            'controller'    => 'profile',
            'id'            => $id,
        ));
        $url = Pi::url($url, true);
        return $url;
    }

    /**
     * Get user login URL
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $url = Pi::service('url')->assemble('user', array(
            'controller'    => 'login'
        ));
        return $url;
    }

    /**
     * Get user logout URL
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        $url = Pi::service('url')->assemble('user', array(
            'controller'    => 'login',
            'action'        => 'logout',
        ));
        return $url;
    }

    /**
     * Get user register URL
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        $url = Pi::service('url')->assemble('user', array(
            'controller'    => 'register',
        ));
        return $url;
    }
    /**#@-*/

    /**#@+
     * Profile APIs
     */
    /**
     * {@inheritDoc}
     */
    public function getName($id = null)
    {
        $name = $this->getUser($id)->name;
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function ____getAvatar($id = null, $size = '', $attributes = array())
    {
        switch ($size) {
            case 'mini':
            case 'xsmall':
            case 'medium':
            case 'large':
            case 'xlarge':
            case 'xxlarge':
                $folder = $size;
                break;
            case 'o':
            case 'original':
                $folder = 'original';
                break;
            case 'normal':
            default:
                $folder = 'normal';
                break;
        }
        $user = $this->getUser($id);
        if (!$user->avatar) {
            $path = sprintf('static/avatar/%s.jpg', $folder);
        } else {
            $path = sprintf('upload/avatar/%s/%s', $folder, $user->avatar);
        }
        $src = Pi::url($path);

        if (false === $attributes) {
            $result = $src;
        } else {
            if (is_string($attributes)) {
                $attributes = array(
                    'alt'   => $attributes,
                );
            } elseif (!isset($attributes['alt'])) {
                $attributes['alt'] = '';
            }
            $attrs = '';
            foreach ($attributes as $key => $val) {
                $attrs .= ' ' . $key . '="' . $val . '"';
            }
            $img = '<img src="%s"%s />';
            $result = sprintf($img, $src, $attrs);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function ____getGravatar($id = null, $size = 80, $attributes = array())
    {
        $user = $this->getUser();
        $email = $this->getUser($id)->email;
        $src = 'http://www.gravatar.com/avatar/%s?s=%d&d=mm&r=g';
        $hash = md5(strtolower($email));
        $src = sprintf($src, $hash, $size);
        if (false === $attributes) {
            $result = $src;
        } else {
            if (is_string($attributes)) {
                $attributes = array(
                    'alt'   => $attributes,
                );
            } elseif (!isset($attributes['alt'])) {
                $attributes['alt'] = '';
            }
            $attrs = '';
            foreach ($attributes as $key => $val) {
                $attrs .= ' ' . $key . '="' . $val . '"';
            }
            $img = '<img src="%s"%s />';
            $result = sprintf($img, $src, $attrs);
        }

        return $result;
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