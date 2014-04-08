<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Uclient\Api;

use Pi;
use Module\System\Api\AbstractUser as AbstractUseApi;
use Pi\User\Model\Client as UserModel;
use Pi\User\Resource\AbstractResource;

/**
 * User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractUseApi
{
    /** @var string Route for user URLs */
    protected $route = 'default';

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
            if (is_array($result) && isset($result[$name])) {
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
            $$this->meta = (array) Pi::service('remote')
                ->setAuthorization($this->config('authorization'))
                ->get($uri);
        }

        return $this->meta;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser($uid, $field = 'id')
    {
        $user = new UserModel($uid, $field);
        if ($uid && is_scalar($uid) && !$user->id) {
            $user = null;
        }

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
            array_walk($condition, function ($value, $key) use (&$query) {
                $query[] = $key . ':' . $value;
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

        $result = Pi::service('remote')
            ->setAuthorization($this->config('authorization'))
            ->get($uri, $params);

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
            array_walk($condition, function ($value, $key) use (&$query) {
                $query[] = $key . ':' . $value;
            });
            $params['query'] = implode(',', $query);
        }

        $result = Pi::service('remote')
            ->setAuthorization($this->config('authorization'))
            ->get($uri, $params);
        $count = (int) $result['data'];

        return $count;
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
    public function get(
        $uid,
        $field = array(),
        $filter = false,
        $activeOnly = false
    ) {
        if (!$uid) {
            return false;
        }
        if (is_scalar($uid)) {
            $uri = $this->config('url', 'get');
            $user = $uid;
        } else {
            $uri = $this->config('url', 'mget');
            $user = implode(',', $uid);
        }
        $params = array(
            'id'    => $user,
        );
        if ($field) {
            $params['field'] = implode(',', (array) $field);
        }
        $result = Pi::service('remote')
            ->setAuthorization($this->config('authorization'))
            ->get($uri, $params);
        if ($field && is_scalar($field)) {
            if (is_scalar($uid)) {
                $result = $result[$field];
            } else {
                array_walk($result, function (&$user) use ($field) {
                    $user = $user[$field];
                });
            }
        }

        return $result;
    }

    /**
     * Get field value(s) of users
     *
     * @param int[]             $uids
     * @param string|string[]   $field
     * @param bool              $filter
     * @param bool              $activeOnly
     *
     * @return mixed[]
     * @api
     */
    public function mget(
        array $uids,
        $field = array(),
        $filter = false,
        $activeOnly = false
    ) {
        $result = $this->get($uids, $field, $filter, $activeOnly);

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
        $section = $section ?: Pi::engine()->application()->getSection();
        $roles = parent::getRole($uid, $section);
        if ($uid && 'front' == $section) {
            if (!in_array('member', $roles)) {
                $roles[] = 'member';
            }
        }

        return $roles;
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
        $meta = Pi::registry('compound_field', 'user')->read($compound);
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

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $var = null)
    {
        $redirect   = '';
        switch ($type) {
            case 'avatar':
                $type = $var ?: 'get';
                $url = $this->config('url', 'avatar', $type);
                break;

            case 'login':
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $redirect = $params['redirect'];
                    unset($params['redirect']);
                } else {
                    $redirect = Pi::service('url')->getRequestUri();
                }
                if (isset($params['section'])) {
                    $section = $params['section'];
                    unset($params['section']);
                } else {
                    $section = Pi::engine()->application()->getSection();
                }
                if ('admin' == $section) {
                    $route = 'admin';
                    if (!isset($params['module'])) {
                        $params['module'] = 'system';
                    }
                    if (!isset($params['controller'])) {
                        $params['controller'] = 'login';
                    }
                    if (isset($params['route'])) {
                        //$route = $params['route'];
                        unset($params['route']);
                    }
                    $url = Pi::service('url')->assemble($route, $params);
                } else {
                    //$url = $this->config('url', 'login');
                    $url = Pi::service('authentication')->getUrl('login', $redirect);
                    $redirect = '';
                }
                break;

            case 'logout':
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $redirect = $params['redirect'];
                    unset($params['redirect']);
                }
                if (isset($params['section'])) {
                    $section = $params['section'];
                    unset($params['section']);
                } else {
                    $section = Pi::engine()->application()->getSection();
                }
                if ('admin' == $section) {
                    $route = 'admin';
                    if (!isset($params['module'])) {
                        $params['module'] = 'system';
                    }
                    if (!isset($params['controller'])) {
                        $params['controller'] = 'login';
                    }
                    if (!isset($params['action'])) {
                        $params['action'] = 'logout';
                    }
                    if (isset($params['route'])) {
                        //$route = $params['route'];
                        unset($params['route']);
                    }
                    $url = Pi::service('url')->assemble($route, $params);
                } else {
                    //$url = $this->config('url', 'logout');
                    $url = Pi::service('authentication')->getUrl('logout');
                    $redirect = '';
                }
                break;

            case 'register':
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $redirect = $params['redirect'];
                    unset($params['redirect']);
                } else {
                    $redirect = Pi::service('url')->getRequestUri();
                }
                $url = $this->config('url', 'register');
                break;

            case 'password':
                if (is_string($var)) {
                    $params = array(
                        'action' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                $params['module'] = 'uclient';
                $params['controller'] = 'password';
                if (!isset($params['action'])) {
                    $params['action'] = 'find';
                }
                $route = 'default';
                $url = Pi::service('url')->assemble($route, $params);
                break;

            case 'profile':
            case 'home':
            default:
                $type = $type ?: 'profile';
                $params = array();
                if (is_numeric($var)) {
                    $params['id'] = (int) $var;
                } elseif (is_string($var)) {
                    $params['name'] = $var;
                } else {
                    $params = (array) $var;
                }
                if (!empty($params['id'])) {
                    $url = $this->config('url', $type, 'id');
                    $url = sprintf($url, $params['id']);
                } elseif (!empty($params['name'])) {
                    $url = $this->config('url', $type, 'name');
                    $url = sprintf($url, $params['name']);
                } elseif (!empty($params['identity'])) {
                    $url = $this->config('url', $type, 'identity');
                    $url = sprintf($url, $params['identity']);
                } else {
                    $url = $this->config('url', $type, 'my');
                }
                break;
        }

        // Append redirect with query
        // @see http://httpd.apache.org/docs/2.2/mod/core.html#allowencodedslashes
        if ($redirect) {
            $redirect = Pi::url($redirect, true);
            if (false == strpos($url, '?')) {
                $url .= '?redirect=' . rawurlencode($redirect);
            } else {
                $url .= '&redirect=' . rawurlencode($redirect);
            }
        }

        return $url;
    }

    /**
     * Get resource handler or result from handler if args specified
     *
     * @param string $name
     *
     * @return AbstractResource
     */
    public function getResource($name)
    {
        $class = 'Module\Uclient\Api\Resource\\' . ucfirst($name);
        if (!class_exists($class)) {
            $class = 'Pi\User\Resource\\' . ucfirst($name);
        }
        $resource = new $class;
        $clientConfig = Pi::api('user', 'uclient')->config();
        $config = array(
            'app_key'       => $this->config('app_key'),
            'authorization' => $this->config('authorization'),
        );
        $options = $this->config($name);
        if (!empty($options))  {
            $config = array_merge($config, $options);
        }
        $resource->setOptions($config);

        return $resource;
    }
}
