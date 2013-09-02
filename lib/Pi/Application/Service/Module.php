<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Module handling service
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
        'config'    => array()
    );

    /**
     * Constructor
     *
     * @param array $options
     *      Parameters to send to the service during instanciation
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
     * @return array
     */
    public function createMeta()
    {
        $meta = array();
        $rowset = Pi::model('module')->select(array());
        foreach ($rowset as $row) {
            $meta[$row->name] = array(
                'directory' => $row->directory,
                'active'    => $row->active,
            );
        }

        $configFile = Pi::path('config') . '/' . $this->fileMeta;
        clearstatcache();
        if (!file_exists($configFile)) {
            touch($configFile);
        } elseif (!is_writable($configFile)) {
            @chmod($configFile, intval('0777', 8));
        }
        $content = '<?php' . PHP_EOL
                 . 'return ' . var_export($meta, true) . ';' . PHP_EOL;
        file_put_contents($configFile, $content);
        @chmod($configFile, intval('0444', 8));
        clearstatcache();

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
            /*
            if (!$list) {
                $list = $this->createMeta();
            }
            */
            $this->container['meta'] = $list;
        }

        return true;
    }

    /**
     * Get module meta data
     *
     * @param string $module
     * @return array|bool
     */
    public function meta($module = null)
    {
        //$this->init();
        if (null === $module) {
            $return = $this->container['meta'];
        } elseif (isset($this->container['meta'][$module])) {
            $return = $this->container['meta'][$module];
        } else {
            $return = false;
        }

        return $return;
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
     * @return array
     */
    public function config($key = null, $module = null)
    {
        $module = $module ?: $this->currentModule;
        if (!isset($this->container['config'][$module])) {
            $this->container['config'][$module] =
                Pi::registry('config')->read($module);
        }
        return $key
            ? $this->container['config'][$module][$key]
            : $this->container['config'][$module];
    }

    /**
     * Load meta data of a module and category
     *
     * @param string $module
     * @param string $type
     * @return array
     */
    public function loadMeta($module, $type = null)
    {
        Pi::service('i18n')->translator->load(sprintf(
            'module/%s:meta',
            $module
        ));
        $configFile = sprintf('%s/config/module.php', $this->path($module));
        $config = include $configFile;

        // For backward compat
        if (isset($config['maintenance'])) {
            if (isset($config['maintenance']['resource'])) {
                $config['resource'] = $config['maintenance']['resource'];
            }
            unset($config['maintenance']);
        }

        if ($type) {
            if (isset($config[$type])) {
                $result = $config[$type];
            } elseif (isset($config['resource'][$type])) {
                $result = $config['resource'][$type];
            } else {
                $result = array();
            }
        } else {
            $result = $config;
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
     * However, when module clone happends, which is implemented in Pi Engine
     * a module's directory is its parent or root module's folder name
     * while folder or `dirname` by tradition is its key name.
     *
     * @param string $module Module's dirname or identifier name
     * @return string
     */
    public function directory($module = null)
    {
        $module = $module ?: $this->currentModule;
        $directory = false;
        if (isset($this->container['meta'][$module])) {
            $directory = $this->container['meta'][$module]['directory'];
        } else {
            $directory = $module;
        }

        return $directory;
    }

    /**
     * Fetch content of an item from a type of moldule content by calling
     * `Module\<ModuleName>\Service::content()`
     *
     * @param array $variables  array of variables to be returned:
     *                          title, summary, uid, user, etc.
     * @param array $conditions associative array of conditions:
     *                          item - item ID or ID list, module, type - optional, user, Where
     *
     * @throws \Exception
     * @return  array Associative array of returned content,
     *      or list of associative array if $item is an array
     */
    public function content(array $variables, array $conditions)
    {
        if (!isset($conditions['module'])) {
            throw new \Exception('module is required.');
        }
        $directory = $this->directory($conditions['module']);
        $class = sprintf('Module\\%s\Service', ucfirst($directory));
        if (!class_exists($class)) {
            return false;
        }

        return $class::content($variables, $conditions);
    }
}
