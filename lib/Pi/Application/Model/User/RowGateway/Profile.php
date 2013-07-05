<?php
/**
 * Pi User Profile Model Row
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
 * @subpackage      Model
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Model\User\RowGateway;
use Pi;
use Pi\Db\RowGateway\RowGateway;

class Profile extends RowGateway
{
    protected static $meta;

    protected function getMeta($key)
    {
        if (!isset(static::$meta)) {
            static::$meta = Pi::service('registry')->user->read();
        }
        $meta =& static::$meta[$key];
        if (!empty($meta['method'])) {
            if (is_array($meta['method'])) {
                if (!class_exists($meta['method'][0]) || !is_callable($meta['method'])) {
                    $meta['method'] = null;
                }
            } else {
                $meta['method'] = null;
            }
        }
        return $meta;
    }

    public function display($col = null)
    {
        $result = array();
        if (!isset($col)) {
            foreach (array_keys($this->_data) as $key) {
                $ret = $this->transformMeta($key);
                if (!is_null($ret)) {
                    $result[$key] = $ret;
                }
            }
        } else {
            $result = $this->transformMeta($col);
        }

        return $result;
    }

    protected function transformMeta($key)
    {
        $value = $this->{$key};
        if (!is_null($value)) {
            $meta = static::getMeta($key);
            if (isset($meta['method'])) {
                if (empty($meta['method'])) {
                    $value = null;
                } elseif (is_array($meta['method'])) {
                    $value = call_user_func($meta['method'], $value);
                } elseif (empty($value)) {
                    //$value = null;
                }
            } elseif (isset($meta['options'][$value])) {
                $value = $meta['options'][$value];
            } elseif (empty($value)) {
                //$value = null;
            }
        }
        return $value;
    }
}
