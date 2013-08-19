<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model\RowGateway;

use Pi;
use Pi\Db\RowGateway\RowGateway;

/**
 * User profile abstract row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractFieldRowGateway extends RowGateway implements
    DisplayInterface
{
    /** @var string Model type */
    protected static $type = '';

    /**
     * Profile meta data
     * @var array
     */
    protected static $meta;

    /**
     * Get meta data of a key or all set
     *
     * @param string|null $key
     * @return array
     */
    protected function getMeta($key = null)
    {
        if (!isset(static::$meta)) {
            static::$meta = Pi::registry('profile', 'user')->read(
                static::$type
            );
        }
        if ($key) {
            $result = isset(static::$meta[$key]) ? static::$meta[$key] : null;
        } else {
            $result = static::$meta;
        }

        return $result;
    }

    /**
     * Get value of a column for display
     *
     * @param string $col
     * @return string|mixed[]
     */
    public function display($col = null)
    {
        $result = array();
        if (!isset($col)) {
            foreach (array_keys(static::getMeta()) as $key) {
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
            if (isset($meta['filter'])) {
                $filter = new $meta['filter'];
                $value = $filter($value);
            }
        }

        return $value;
    }
}
