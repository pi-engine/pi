<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Uclient\Api;

use Pi;
use Module\System\Api\AbstractUser as AbstractUseApi;
use Pi\Db\Sql\Where;
use Pi\User\Model\Client as UserModel;

/**
 * User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractUseApi
{
    /** @var string Module name */
    protected $module = 'uclient';

    /** @var string Config file name */
    protected $configFile = 'module.uclient.php';

    /** @var  array Config for remote access */
    protected $config;

    /** @var  array User profile meta */
    protected $meta;

    /**
     * Get an option
     *
     * @return mixed|null
     */
    public function config()
    {
        if (null === $this->config) {
            $this->config = Pi::service('config')->load($this->configFile);
        }
        $args = func_get_args();
        $result = $this->config;
        foreach ($args as $name) {
            if (isset($result[$name])) {
                $result = $result[$name];
            } else {
                $result = null;
                break;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getMeta($type = '', $action = '')
    {
        if (null === $this->meta) {
            $uri = $this->config('url', 'meta');
            $$this->meta = (array) Pi::service('remote')->get($uri);
        }

        return $this->meta;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser($uid, $field = 'id')
    {
        $user = new UserModel($uid, $field);

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * @param array  $condition
     */
    public function getUids(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = ''
    ) {
        $result = $this->getList(
            $condition,
            $limit,
            $offset,
            $order,
            array('id')
        );
        array_walk($result, function (&$data) {
            return (int) $data['id'];
        });

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @param array  $condition
     *
     * @throw InvalidArgumentException
     */
    public function getList(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = '',
        $field      = array()
    ) {
        if (!is_array($condition)) {
            throw new \InvalidArgumentException('Array type required.');
        }
        $uri = $this->config('url', 'list');
        $params = array();
        if ($condition) {
            $query = array();
            array_walk($condition, function ($value, $key) {
                return $key . ':' . $value;
            });
            $params['query'] = implode(',', $query);
        }
        if ($limit) {
            $params['limit'] = (int) $limit;
        }
        if ($offset) {
            $params['offset'] = (int) $offset;
        }
        if ($order) {
            $params['order'] = implode(',', (array) $order);
        }
        if ($field) {
            $params['field'] = implode(',', (array) $field);
        }

        $result = Pi::service('remote')->get($uri, $params);

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @param array  $condition
     *
     * @throw InvalidArgumentException
     */
    public function getCount($condition = array())
    {
        if (!is_array($condition)) {
            throw new \InvalidArgumentException('Array type required.');
        }
        $uri = $this->config('url', 'count');
        $params = array();
        if ($condition) {
            $query = array();
            array_walk($condition, function ($value, $key) {
                return $key . ':' . $value;
            });
            $params['query'] = implode(',', $query);
        }

        $result = (int) Pi::service('remote')->get($uri, $params);

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function addUser($data, $setRole = true)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function updateUser($uid, array $data)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function deleteUser($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function activateUser($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function enableUser($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function disableUser($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function get($uid, $field, $filter = false)
    {
        if (!$uid) {
            return false;
        }
        if (is_scalar($uid)) {
            $uri = $this->config('url', 'get');
        } else {
            $uri = $this->config('url', 'mget');
            $uid = implode(',', $uid);
        }
        $params = array(
            'id'    => $uid,
        );
        if ($field) {
            $params['field'] = implode(',', (array) $field);
        }
        $result = Pi::service('remote')->get($uri, $params);
        if ($field && is_scalar($field)) {
            array_walk($result, function ($user) use ($field) {
                return $user[$field];
            });
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function set($uid, $field, $value)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setRole($uid, $role)
    {
        return parent::setRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeRole($uid, $role)
    {
        return parent::revokeRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function getRole($uid, $section = '')
    {
        return parent::getRole($uid, $section);
    }

    /**
     * {@inheritDoc}
     */
    public function canonizeField(array $fields)
    {
        $meta = array();
        $fieldMeta = $this->getMeta();
        foreach ($fields as $field) {
            if (isset($fieldMeta[$field])) {
                $meta[$fieldMeta[$field]['type']][] = $field;
            }
        }

        return $meta;
    }

    /**
     * {@inheritDoc}
     */
    public function canonizeCompound(
        $uid,
        $compound,
        array $rawData,
        $set = 0
    ) {
        $meta = Pi::registry('compound', 'user')->read($compound);
        $canonizeSet = function ($data, $set) use ($uid, $compound, $meta) {
            $result = array();
            foreach (array_keys($data) as $key) {
                if (!isset($meta[$key])) {
                    unset($data[$key]);
                    continue;
                }
                $result[] = array(
                    'uid'       => $uid,
                    'compound'  => $compound,
                    'field'     => $key,
                    'set'       => $set,
                    'value'     => $data[$key],
                );
            }

            return $result;
        };

        $result = array();
        if (is_int(key($rawData))) {
            $set = 0;
            foreach ($rawData as $data) {
                $result = array_merge($result, $canonizeSet($data, $set));
                $set++;
            }
        } else {
            $result = $canonizeSet($rawData, $set);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function canonizeUser(array $rawData, $type = '')
    {
        $result = array();

        $meta = $this->getMeta($type);
        foreach ($rawData as $key => $value) {
            if (isset($meta[$key])) {
                if ($type) {
                    $result[$key] = $value;
                } else {
                    $result[$meta[$key]['type']][$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function addAccount(array $data)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function updateAccount($uid, array $data)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function deleteAccount($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function activateAccount($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function enableAccount($uid, $flag = true)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function addProfile($uid, array $data)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function updateProfile($uid, array $data)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function deleteProfile($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function addCompound($uid, array $data)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function updateCompound($uid, array $data)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return false
     */
    public function deleteCompound($uid)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getFields($uid, $type, $fields = array(), $filter = false)
    {
        $result = $this->get($uid, $type, $fields, $filter);

        return $result;
    }
}
