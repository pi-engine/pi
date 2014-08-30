<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Module handling service
 *
 * Usage:
 *
 * - Get meta of a module
 * ```
 *  $title = Pi::module()->meta(<module_name>, 'title');
 *  $version = Pi::module()->meta(<module_name>, 'version');
 *  $active = Pi::module()->meta(<module_name>, 'active');
 *
 *  $directory = Pi::module()->meta(<module_name>, 'directory');
 *  $directory = Pi::module()->directory(<module_name>);
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Module extends AbstractService
{
    /** @var string File of installed module meta data */
    protected $fileMeta = 'module.meta.php';

    /** {@inheritDoc} */
    protected $fileIdentifier = 'module';

    /** @var string Current module */
    protected $currentModule;

    /** @var array Container of module meta */
    protected $container = array(
        // Meta of modules: directory, active, path
        'meta'  => array(),
        // module models
        //'model'     => array(),
        // module configs
        //'config'    => array()
    );

    /**
     * Constructor
     *
     * @param array $options
     *      Parameters to send to the service during instantiation
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->init();
    }

    /**
     * Set current active module
     *
     * @param string $module
     * @return self
     */
    public function setModule($module)
    {
        $this->currentModule = $module;

        return $this;
    }

    /**
     * Get current active module
     *
     * @return string
     */
    public function current()
    {
        return $this->currentModule;
    }

    /**
     * Get path to file containing module meta data
     *
     * @return string
     */
    public function getMetaFile()
    {
        return $this->fileMeta;
    }

    /**
     * Create module meta data fetching from DB and write to meta data
     *
     * @return array Array of `directory`, `title`, `version`, `active`
     */
    public function createMeta()
    {
        $meta = array();
        $rowset = Pi::model('module')->select(array());
        foreach ($rowset as $row) {
            $meta[$row->name] = array(
                'directory' => $row->directory,
                'title'     => $row->title,
                'version'   => $row->version,
                'active'    => (int) $row->active,
            );
        }

        $status = Pi::service('config')->write($this->fileMeta, $meta);
        $this->init(true);

        return $meta;
    }

    /**
     * Initialize the service: load meta data from meta file
     *
     * @param bool $force Force to re-generate module meta data
     * @return bool
     */
    public function init($force = false)
    {
        if ($force || empty($this->container['meta'])) {
            $list = Pi::config()->load($this->fileMeta);
            $this->container['meta'] = $list;
        }

        return true;
    }

    /**
     * Get module meta data
     *
     * @param string $module
     * @param string $key Valid keys: `directory`, `title`, `version`, `active`
     *
     * @return array|bool
     */
    public function meta($module = '', $key = '')
    {
        if (!$module) {
            $result = $this->container['meta'];
        } elseif (!isset($this->container['meta'][$module])) {
            $result = false;
        } elseif ($key) {
            $result = isset($this->container['meta'][$module][$key])
                ? $this->container['meta'][$module][$key]
                : false;
        } else {
            $result = $this->container['meta'][$module];
        }

        return $result;
    }

    /**
     * Check if a module is active
     *
     * @param string $module
     * @return bool
     */
    public function isActive($module)
    {
        return empty($this->container['meta'][$module]['active'])
            ? false : true;
    }

    /**
     * Get config of a module and category
     *
     * @param string $key
     * @param string $module
     *
     * @return array
     * @deprecated
     */
    public function config($key = '', $module = '')
    {
        return Pi::config($key, $module);
    }

    /**
     * Load meta data of a module and category
     *
     * @param string    $module
     * @param string    $type
     * @param bool      $fetch  Fetch resource meta from config file
     *
     * @return array
     */
    public function loadMeta($module, $type = null, $fetch = false)
    {
        Pi::service('i18n')->load(array('module/' . $module, 'admin'));

        // Load module meta data
        $configFile = sprintf('%s/config/module.php', $this->path($module));
        $config = include $configFile;

        // For backward compatibility
        if (isset($config['maintenance'])) {
            if (isset($config['maintenance']['resource'])) {
                $config['resource'] = $config['maintenance']['resource'];
            }
            unset($config['maintenance']);
        }

        // Get resource
        if (isset($config['resource'])) {
            $resource = $config['resource'];
            unset($config['resource']);
        } else {
            $resource = array();
        }

        // Load module custom meta if available
        $resourceCustom     = array();
        $directory          = $this->directory($module);
        $configFileCustom   = sprintf('%s/module/%s/config/module.php', Pi::path('custom'), $directory);
        if (file_exists($configFileCustom)) {
            $configCustom = include $configFileCustom;
            if (!empty($configCustom['meta']['build'])) {
                $config['meta']['version'] .= '+' . $configCustom['meta']['build'];
                unset($configCustom['meta']['build']);
                if (isset($configCustom['resource'])) {
                    $resourceCustom = $configCustom['resource'];
                }
                $config['meta'] = array_replace(
                    $config['meta'],
                    $configCustom['meta']
                );
            }
        }

        // Fetch resource meta
        $getResource = function ($name = null) use (
            $resource,
            $resourceCustom,
            $directory,
            $fetch,
            &$getResource
        ) {
            $result = null;
            if (!$name) {
                $list = array_unique(
                    array_keys($resource) + array_keys($resourceCustom)
                );
                foreach ($list as $key) {
                    $result[$key] = $getResource($key);
                }
            } else {
                if (isset($resourceCustom[$name])) {
                    if (is_string($resourceCustom[$name]) && $fetch) {
                        $file = Pi::path('custom') . '/module/' . $directory . '/config/'
                              . $resourceCustom[$name];
                        if (file_exists($file)) {
                            $result = include $file;
                            if (!is_array($result)) {
                                $result = array();
                            }
                        }
                    } else {
                        $result = $resourceCustom[$name];
                    }
                }
                if (null === $result && isset($resource[$name])) {
                    if (is_string($resource[$name]) && $fetch) {
                        $file = Pi::path('module') . '/' . $directory . '/config/'
                            . $resource[$name];
                        if (file_exists($file)) {
                            $result = include $file;
                            if (!is_array($result)) {
                                $result = array();
                            }
                        }
                    } else {
                        $result = $resource[$name];
                    }
                }
            }

            return $result;
        };

        if ($type) {
            if (isset($config[$type])) {
                if ('resource' == $type) {
                    $result = $getResource();
                } else {
                    $result = $config[$type];
                }
            } else {
                $result = $getResource($type);
            }
        } else {
            $result = $config;
            $result['resource'] = $getResource();
        }

        return $result;
    }

    /**
     * Get path to a module
     *
     * @param string $module
     * @return string
     */
    public function path($module)
    {
        if (isset($this->container['meta'][$module])) {
            $module = $this->container['meta'][$module]['directory'];
        }
        $path = Pi::path('module') . '/' . $module;

        return $path;
    }

    /**
     * Gets a module's physical directory name.
     *
     * Usually a module's directory is equal to its folder name.
     * However, when module clone happens, which is implemented in Pi Engine
     * a module's directory is its parent or root module's folder name
     * while folder or `dirname` by tradition is its key name.
     *
     * @param string $module Module's dirname or identifier name
     * @return string
     */
    public function directory($module = null)
    {
        $module = $module ? strtolower($module) : $this->currentModule;
        if (isset($this->container['meta'][$module])) {
            $directory = $this->container['meta'][$module]['directory'];
        } else {
            $directory = $module;
        }

        return $directory;
    }

    /**
     * Fetch content of an item from a type of module content by calling
     * `Module\<ModuleName>\Api\Content::getList()`
     *
     * @param array $variables  array of variables to be returned:
     *                          title, summary, uid, time, etc.
     * @param array $conditions associative array of conditions:
     *                          item - item ID or ID list, module, type - optional, user, Where
     * @param int           $limit
     * @param int           $offset
     * @param string|array  $order
     *
     * @throws \Exception
     * @return  array Associative array of returned content,
     *      or list of associative array if $item is an array
     */
    public function content(
        array $variables,
        array $conditions,
        $limit  = 0,
        $offset = 0,
        $order  = array()
    ) {
        if (!isset($conditions['module'])) {
            throw new \Exception('module is required.');
        }
        $api = Pi::api('content', $conditions['module']);
        if ($api) {
            $result = $api->getList(
                $variables,
                $conditions,
                $limit,
                $offset,
                $order
            );
        } else {
            $result = array();
        }

        return $result;
    }
}
