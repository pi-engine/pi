<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\User\RowGateway;

use Pi;
use Pi\Db\RowGateway\RowGateway;

/**
 * User profile row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends RowGateway
{
    /**
     * Profile meta data
     * @var array
     */
    protected static $meta;

    /**
     * Get meta data of a key
     *
     * @param string $key
     * @return array
     */
    protected function getMeta($key)
    {
        if (!isset(static::$meta)) {
            static::$meta = Pi::service('registry')->user->read();
        }
        $meta =& static::$meta[$key];
        if (!empty($meta['method'])) {
            if (is_array($meta['method'])) {
                if (!class_exists($meta['method'][0])
                    || !is_callable($meta['method'])) {
                    $meta['method'] = null;
                }
            } else {
                $meta['method'] = null;
            }
        }
        return $meta;
    }

    /**
     * Get value of a column for display
     *
     * @param string $col
     * @return string
     */
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

    /**
     * Transform a meat
     *
     * @param string $key
     * @return mixed
     */
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
