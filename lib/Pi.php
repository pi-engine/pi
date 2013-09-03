<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

use Pi\Application\Host;
use Pi\Application\Persist;
use Pi\Application\Autoloader;
use Pi\Application\Engine\AbstractEngine;
use Pi\Application\Service;
use Pi\Application\Service\AbstractService;
use Pi\Application\Config;
use Pi\Application\Db;
use Pi\Debug\Debug;
use Pi\Utility\Filter;
use Pi\Application\Service\User;
use Pi\Application\Model\Model;
use Pi\Application\Registry\AbstractRegistry;
use Pi\Application\AbstractApi;

/**
 * Pi Engine
 *
 * System bootstrap and global interfaces to applications and modules
 *
 * Boot up process:
 *
 * - init: instantiate and initialize global APIs
 *
 *   - host()
 *   - config()
 *   - persist()
 *   - autoloader()
 *   - engine()
 *   - register self::shutdown()
 *
 * - boot: boot up engine
 *
 *   - config()
 *   - boot()
 *
 * Global APIs:
 *
 *  - autoloader()
 *  - config()
 *  - db()
 *  - engine()
 *  - host()
 *  - model()
 *  - path()
 *  - persist()
 *  - registerShutdown()
 *  - entity()
 *  - service()
 *  - url()
 *  - user()
 *  - registry()
 *  - api()
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Pi
{
    /**
     * Default application engine
     * @var string
     */
    const DEFAULT_APPLICATION_ENGINE = 'standard';

    /**
     * Default application environment
     * @var string
     */
    const DEFAULT_APPLICATION_ENV = 'production';

    /**
     * Path to library root
     * @var string
     */
    const PATH_LIB = PI_PATH_LIB;

    /**
     * Reference to application host
     * @var Host
     */
    protected static $host = null;

    /**
     * Reference to persist handler
     * @var Persist
     */
    protected static $persist = null;

    /**
     * Reference to autoloader handler
     * @var Autoloader
     */
    protected static $autoloader = null;

    /**
     * Reference to application engine
     * @var AbstractEngine
     */
    protected static $engine = null;

    /**
     * Reference to service handler
     * @var Service
     */
    protected static $service = null;

    /**
     * Reference to config handler
     * @var Config
     */
    protected static $config = null;

    /**
     * Reference to Db handler
     * @var Db
     */
    protected static $db = null;

    /**
     * Entity container
     * @var array
     */
    protected static $entity = array();

    /**
     * Shutdown callback container
     * @var array
     */
    protected static $shutdown = array();

    /**
     * Start time
     * @var float
     */
    protected static $start;

    /**
     * Run environment
     * @var string
     */
    protected static $environment = null;

    /**
     * Initialize system environment and APIs
     *
     * Tasks:
     *
     *  1. Instantiate host handler and load host data
     *  2. Load engine general config data which applicable to all applications
     *  3. Load persist handler with persist config data from general config
     *  4. Instantiate autoloader with config data from general config
     *  5. Instantiate application engine with application config data
     */
    public static function init()
    {
        // Set start time
        static::$start = microtime(true);

        // Initialize Host
        $config = array(
            'host'  => array(
                'path'  => array(
                    'lib'   => constant('PI_PATH_LIB'),
                ),
            ),
        );
        if (constant('PI_PATH_HOST')) {
            $config['file'] = constant('PI_PATH_HOST');
        }
        static::host($config);

        // Register autoloader, host, persist and autoloader
        $paths = static::host()->get('path');
        $options = array(
            // Top namespaces
            'top'           => array(
                'Pi'    => static::path('lib') . '/Pi',
                'Zend'  => static::path('lib') . '/Zend',
            ),
            // Regular namespaces
            'namespace'     => array(
            ),
            // Class map
            'class_map'     => array(
            ),
            // Directory of modules
            'module_path'   => static::path('module'),
            // Directory of extras
            'extra_path'    => !empty($paths['extra'])
                               ? $paths['extra']
                               : static::path('usr') . '/extra',
            // Vendor directory
            'include_path'  => !empty($paths['vendor'])
                               ? $paths['vendor']
                               : static::path('lib') . '/vendor',
        );
        static::autoloader($options);

        // Load debugger and filter
        Debug::load();
        Filter::load();

        // Load engine global config
        $engineConfig = static::config()->load('engine.php');
        if (isset($engineConfig['config'])) {
            static::config()->setConfigs($engineConfig['config']);
        }

        // Initialize Persist handler
        $persistConfig = empty($engineConfig['persist'])
                         ? array() : $engineConfig['persist'];
        static::persist($persistConfig);
        // Set persist handler for class/file map
        if (static::persist()->isValid()) {
            static::autoloader()->setPersist(static::persist());
        }

        // Register shutdown functions
        register_shutdown_function(__CLASS__ . '::shutdown');
    }

    /**
     * Verify application environment
     *
     * Priority of different entries
     *
     *  1. Load specified in file via `define('APPLICATION_ENV', <evn-value>)`;
     *  2. Load specified value via `Pi::environment('<env-value>')`;
     *  3. Load via `getenv('APPLICATION_ENV')`
     *      - set in `.htaccess` via `SetEnv APPLICATION_ENV <env-value>`;
     *  4. Load from system config via `Pi::config('environment')`
     *      - set in `var/config/engine.php`.
     *
     * @param string|null $environment
     * @return null|string
     * @api
     */
    public static function environment($environment = null)
    {
        if (null !== $environment) {
            static::$environment = $environment;
            return;
        }

        $result = static::DEFAULT_APPLICATION_ENV;
        if (defined('APPLICATION_ENV')) {
            $result = constant('APPLICATION_ENV');
        } elseif (static::$environment) {
            $result = static::$environment;
        } elseif (getenv('APPLICATION_ENV')) {
            $result = getenv('APPLICATION_ENV');
        } elseif (static::config('environment')) {
            $result = static::config('environment');
        }

        return $result;
    }

    /**
     * Get start time
     *
     * @return float
     */
    public static function startTime()
    {
        return static::$start;
    }

    /**
     * Perform the boot sequence
     *
     * The following operations are done in order during the boot-sequence:
     *
     * - Load system bootstrap preferences
     * - Load primary services
     * - Application bootstrap
     *
     * @return void
     */
    public static function boot()
    {
        // Instantiate the application engine
        $engine = static::engine();
        // Perform application boot
        $status = $engine->run();
        // Skip registered shutdown on failure
        if (false === $status) {
            //static::$shutdown = array();
        }
    }

    /**
     * Perform registered shutdown sequence
     *
     * @return bool
     */
    public static function shutdown()
    {
        foreach (static::$shutdown as $callback) {
            call_user_func($callback);
        }
    }

    /**
     * Instantiate application host
     *
     * @param string|array $config Host file path or array of config data
     * @return Host
     * @api
     */
    public static function host($config = null)
    {
        if (!isset(static::$host)) {
            if (!class_exists('Pi\Application\Host', false)) {
                require static::PATH_LIB . '/Pi/Application/Host.php';
            }
            static::$host = new Host($config);
        }

        return static::$host;
    }

    /**
     * Loads persistent data handler
     *
     * @param array $config Config for the persist handler
     * @return Persist
     * @api
     */
    public static function persist($config = array())
    {
        if (!isset(static::$persist)) {
            static::$persist = new Persist($config);
        }

        return static::$persist;
    }

    /**
     * Loads autoloader handler
     *
     * @param array $options
     *
     * @return  Autoloader
     */
    public static function autoloader($options = array())
    {
        if (!isset(static::$autoloader)) {
            if (!class_exists('Pi\Application\Autoloader', false)) {
                require static::PATH_LIB . '/Pi/Application/Autoloader.php';
            }
            static::$autoloader = new Autoloader($options);
        }

        return static::$autoloader;
    }

    /**
     * Loads application engine
     *
     * @param string    $type       Application type
     * @param array     $config     Config data for the application
     * @return AbstractEngine
     * @api
     */
    public static function engine($type = '', $config = array())
    {
        if (!isset(static::$engine)) {
            if (!$type) {
                $type = defined('APPLICATION_ENGINE')
                    ? APPLICATION_ENGINE : static::DEFAULT_APPLICATION_ENGINE;
            }
            $appEngineClass = 'Pi\Application\Engine\\' . ucfirst($type);
            static::$engine = new $appEngineClass($config);
        }

        return static::$engine;
    }

    /**
     * Load a service or service handler
     *
     * If service name is not specified, a service placeholder will be returned
     *
     * @param string    $name
     * @param array     $options
     * @return Service|AbstractService
     * @api
     */
    public static function service($name = null, $options = array())
    {
        // service handler
        if (!isset(static::$service)) {
            static::$service = new Service;
        }
        // Return service handler
        if (null === $name) {
            return static::$service;
        }
        // Load a service
        $service = static::$service->load($name, $options);

        return $service;
    }

    /**
     * Load user service
     *
     * @return User
     * @api
     */
    public static function user()
    {
        return static::service('user');
    }

    /**
     * Load registry
     *
     * Usage
     *
     * ```
     *  // Global registry
     *  Pi::registry(<name>)->read(<...>);
     *  // Alias of
     *  Pi::service('registry')-><name>->read(<...>);
     *
     *  // Module registry
     *  Pi::registry(<name>, <module>)->read(<...>);
     *  // Alias of
     *  Pi::service('registry')->handler(<name>, <module>)->read(<...>);
     * ```
     *
     * @param string $name
     * @param string $module
     *
     * @return AbstractRegistry
     * @api
     */
    public static function registry($name, $module = null)
    {
        return static::service('registry')->handler($name, $module);
    }

    /**
     * Load module API
     *
     * Usage
     *
     * ```
     *  // Module default API
     *  Pi::api(<module-name>)->{<method>}(<args>);
     *  // Alias of
     *  Pi::service('api')->handler(<module-name>, 'api')->{<method>}(<args>);
     *
     *  // Module specific API
     *  Pi::api(<module-name>, <api-name>)->{<method>}(<args>);
     *  // Alias of
     *  Pi::service('api')->handler(<module-name>, <api-name>)->{<method>}(<args>);
     * ```
     *
     * @param string $module
     * @param string $api
     *
     * @return AbstractApi
     * @api
     */
    public static function api($module, $api = 'api')
    {
        return static::service('api')->handler($module, $api);
    }

    /**
     * Load database identifier
     *
     * @return Db
     * @api
     */
    public static function db()
    {
        // Instantiate Db handler
        if (!isset(static::$db)) {
            static::$db = static::service('database')->db();
        }

        return static::$db;
    }

    /**
     * Load a core model by name
     *
     * @param string    $name
     * @param string    $module
     * @param array     $options
     * @return Model
     * @api
     */
    public static function model($name, $module = '', $options = array())
    {
        if ($module) {
            $name = $module . '/' . $name;
        }
        return static::db()->model($name, $options);
    }

    /**
     * Load a config by name or return config handler if name is not specified
     *
     * @param string    $name       Name of the config element
     * @param string    $domain     Configuration domain
     * @return Config|mixed    config value or config handler if $name not specified
     * @api
     */
    public static function config($name = null, $domain = null)
    {
        // config handler
        if (!isset(static::$config)) {
            static::$config = new Config(
                static::path('config')
            );
        }
        // Return config handler
        if (null === $name) {
            return static::$config;
        }
        // Read a config
        $value = static::$config->get($name, $domain);

        return $value;
    }

    /**
     * Container for global entities
     *
     * Register a variable to global container, or fetch a glbal entity if
     * variable value is not provided
     *
     * @param string    $index  Name of the entity
     * @param mixed     $value  The value to store.
     * @return void|mixed
     * @api
     */
    public static function entity($index, $value = null)
    {
        $index = strtolower($index);
        if (null !== $value) {
            static::$entity[$index] = $value;
        } else {
            return isset(static::$entity[$index])
                ? static::$entity[$index] : null;
        }
    }

    /**
     * Register a shutdown callback with FILO
     *
     * @param string|array  $callback
     *  Callback method to be called in shutdown
     * @param bool          $toAppend
     *  To append current callback to registered shutdown list, false to prepend
     * @return void
     */
    public static function registerShutdown($callback, $toAppend = false)
    {
        if ($toAppend) {
            static::$shutdown[] = $callback;
        } else {
            array_unshift(static::$shutdown, $callback);
        }
    }

    /**
     * Convert a path to a physical one, proxy to host handler
     *
     * For path value to be examined:
     *
     *  - With `:` or leading slash `/` - absolute path, do not convert;
     *  - Otherwise, first part as section, map to `www` if no section matched
     *
     * @see Host::path()
     * @param string $path  Path to be converted
     *
     * @return string
     * @api
     */
    public static function path($path)
    {
        return static::$host->path($path);
    }

    /**
     * Convert a path to an URL, proxy to host handler
     *
     * For URL to be examined:
     *
     *  - With URI scheme `://` - absolute URI, do not convert;
     *  - First part as section, map to `www` if no section matched;
     *  - If section URI is relative, `www` URI will be appended.
     *
     * @see Host::url()
     * @param string    $url        URL to be converted
     * @param bool      $absolute
     *  Convert to full URI; Default as relative URI with no hostname
     * @return string
     * @api
     */
    public static function url($url, $absolute = false)
    {
        return static::$host->url($url, $absolute);
    }

    /**
     * Logs audit information
     *
     * @param string            $message
     * @param array|Traversable $extra
     * @return void
     * @api
     */
    public static function log($message, $extra = array())
    {
        static::service('log')->audit($message, $extra);
    }

    /**
     * Magic method to load service
     *
     * @param string    $method
     * @param array     $args
     *
     * @return AbstractService|bool
     */
    public static function __callStatic($method, array $args)
    {
        if (count($args) > 1 || ($args && !is_array($args[0]))) {
            return false;
        }
        $options = $args ? $args[0] : array();
        $service = static::service($method, $options);

        return $service;
    }
}

/**
 * Initialize Pi Engine by calling `Pi::init()`
 */
Pi::init();
