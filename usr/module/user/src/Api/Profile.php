<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;
use Pi\Db\RowGateway\RowGateway;

/**
 * User profile manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * Get field names of specific type and action
     *
     * - Available types: `account`, `profile`, `custom`
     * - Available actions: `display`, `edit`, `search`
     *
     * @param string $type
     * @param string $action
     * @return string[]
     * @api
     */
    public function getMeta($type = '', $action = '')
    {
        $fields = Pi::registry('profile', 'user')->read($type, $action);

        return array_keys($fields);
    }

    /**
     * Get field value(s) of a user field(s)
     *
     * @param string|array      $key
     * @param string|int|null   $id
     * @return mixed|mixed[]
     * @api
     */
    public function get($key, $id)
    {

    }

    /**
     * Get field value(s) of a list of user
     *
     * @param string|array      $key
     * @param array             $ids
     * @return array
     * @api
     */
    abstract public function getList($key, $ids);

    /**
     * Set value of a user field
     *
     * @param string            $key
     * @param midex             $value
     * @param string|int|null   $id
     * @return bool
     * @api
     */
    abstract public function set($key, $value, $id);

    /**
     * Incremetn/decrement a user field
     *
     * @param string            $key
     * @param int               $value
     *      Positive to increment or negative to decrement
     * @param string|int|null   $id
     * @return bool
     * @api
     */
    abstract public function increment($key, $value, $id);

    /**
     * Set a user password
     *
     * @param string            $value
     * @param string|int|null   $id
     * @return bool
     * @api
     */
    abstract public function setPassword($value, $id);
}
