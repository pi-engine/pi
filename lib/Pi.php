<?php
/**
 * Pi Kernel Engine
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
 * @since           3.0
 * @package         Pi
 * @version         $Id$
 */

/**
 * Pi Engine
 *
 * Plays the role as system bootstrap and global interfaces to applications and modules
 *
 * Boot up process:
 * init: instantiate and initialize gobal APIs
 *  - host()
 *  - config()
 *  - persist()
 *  - autoloader()
 *  - engine()
 *  - register self::shutdown()
 * boot: boot up engine
 *  - engine->config()
 *  - engine->bootstrap()
 *  - engine->application()
 * bootstrap: boostap application
 *  - bootstrap(application)
 * run: run application and returns response
 *  - run(): route, dispatch
 * send response
 *  - send()
 *
 * Global APIs:
 *  - host()
 *  - config()
 *  - persist()
 *  - autoloader()
 *  - engine()
 *  - service()
 *  - model()
 *  - registerShutdown()
 *  - registry()
 *  - path()
 *  - url()
 *
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Pi
{
    /**
     * @var string
     * @see http://semver.org/ for semantic versioning
     */
    const VERSION = '3.0.0-beta.1';

    /**
     * Default application engine
     * @var string
     * @access public
     */
    const DEFAULT_APPLICATION_ENGINE = 'standard';

    /**
     * Default application environment
     * @var string
     * @access public
     */
    const DEFAULT_APPLICATION_ENV = 'production';

    /**
     * Path to library root
     * @var string
     * @access public
     */
    const PATH_LIB = PI_PATH_LIB;

    /**
     * Path to www root
     * @var string
     * @access public
     */
    const PATH_WWW = PI_PATH_WWW;

    /**
     * Reference to application host
     * @var {@Pi\Application\Host}
     * @access protected
     */
    protected static $host = null;

    /**
     * Reference to persist handler
     * @var {@Pi\Application\Persist}
     * @access protected
     */
    protected static $persist = null;

    /**
     * Reference to autoloader handler
     * @var {@Pi\Application\Autoloader}
     * @access protected
     */
    protected static $autoloader = null;

    /**
     * Reference to application engine
     * @var array of {@Pi\Application\Engine}
     * @access protected
     */
    protected static $engine = null;

    /**
     * Reference to service handler
     * @var {@Pi\Application\Service}
     * @access protected
     */
    protected static $service = null;

    /**
     * Reference to config handler
     * @var {@Pi\Application\Config}
     * @access protected
     */
    protected static $config = null;

    /**
     * Reference to Db handler
     * @var {@Pi\Application\Db}
     * @access protected
     */
    protected static $db = null;

    /**
     * Registry container
     * @var array
     * @access protected
     */
    protected static $registry = array();

    /**
     * Shutdown callback container
     * @var array
     * @access protected
     */
    protected static $shutdown = array();

    /*
     * Start time
     * @var float
     */
    protected static $start;

    /**
     * Run environment
     * @var string
     * @access protected
     */
    protected static $environment = null;

    /**
     * Initialize system environment and APIs
     *
     * Tasks:
     *  1. instantiate host handler and load host data
     *  2. load engine general config data which applicable to all applications
     *  3. instantiate persist handler with persist config data from general config
     *  4. instantiate autoloader handler and load system autoloader with autoloader config data from general config
     *  5. instantiate application engine with application config data from general config
     */
    public static function init()
    {
        static::$start = microtime(true);

        /**#@+
         * Initialize Host
         */
        $config = array();
        $config['host']['path']['lib'] = constant('PI_PATH_LIB');
        $config['host']['path']['www'] = constant('PI_PATH_WWW');
        if (constant('PI_PATH_HOST')) {
            $config['file'] = constant('PI_PATH_HOST');
        }
        static::host($config);
        /**#@-*/

        /**#@+
         * Register autoloader, host and persist handler required,use autoloader config data from general config
         */
        $paths = static::host()->get('path');
        $options = array(
            // Top namespaces
            'top'       => array(
                'Pi' => static::path('lib') . '/Pi',
                'Zend'  => static::path('lib') . '/Zend',
            ),
            // Regular namespaces
            'namespace' => array(
            ),
            // Class map
            'classmap'  => array(
            ),
            // Directory of modules
            'modulepath'    => static::path('module'),
            // Vendor directory
            'includepath'   => !empty($paths['vendor']) ? $paths['vendor'] : static::path('lib') . '/vendor',
        );
        static::autoloader($options);
        /**#@-*/

        // Load debugger
        Pi\Debug::load();

        /**#@+
         * Load engine global config
         */
        $engineConfig = static::config()->load('engine.php');
        if (isset($engineConfig['config'])) {
            static::config()->setConfigs($engineConfig['config']);
        }
        /**#@-*/

        /**#@+
         * Initialize Persist handler
         */
        $persistConfig = empty($engineConfig['persist']) ? array() : $engineConfig['persist'];
        static::persist($persistConfig);
        // Set persist handler for class/file map
        if (static::persist()->isValid()) {
            static::autoloader()->setPersist(static::persist());
        }
        /*#@-*/

        // Register shutdown functions
        register_shutdown_function(__CLASS__ . '::shutdown');
    }

    /**
     * Verify application environment
     *
     * Priority of different entries
     *  1. Specified in file via define('APPLICATION_ENV', 'somevalue');
     *  2. Specified value via Pi::environment('somevalue');
     *  3. Specified via getenv('APPLICATION_ENV') - usually set in .htaccess via "SetEnv APPLICATION_ENV production";
     *  4. Set from system config via Pi::config('environment') set in var/config/engine.php.
     *
     * @param string|null $environment
     * @return null:string
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
        // Performe application boot
        $status = $engine->run();
        // Skip registered shutdown on failure
        if (false === $status) {
            //static::$shutdown = array();
        }
    }

    /**
     * Perform registered shutdown sequence
     *
     * @access public
     * @return bool
     */
    public static function shutdown()
    {
        foreach (static::$shutdown as $callback) {
            call_user_func($callback);
        }
    }

    /**#@+
     * System API
     */
    /**
     * Instantiate application host
     *
     * @param string|array  $config Host file path or array of configuration data
     * @return {@Pi\Application\Host}
     */
    public static function host($config = null)
    {
        if (!isset(static::$host)) {
            if (!class_exists('Pi\\Application\\Host', false)) {
                require static::PATH_LIB . '/Pi/Application/Host.php';
            }
            static::$host = new Pi\Application\Host($config);
        }
        return static::$host;
    }

    /**
     * Loads persistent data handler
     *
     * @param array     $config    Config for the persist handler
     * @return {@Pi\Application\Persist\PersistInterface}
     */
    public static function persist($config = array())
    {
        if (!isset(static::$persist)) {
            static::$persist = new Pi\Application\Persist($config);
        }
        return static::$persist;
    }

    /**
     * Loads autoloader handler
     *
     * @return Pi\Application\Autoloader
     */
    public static function autoloader($options = array())
    {
        if (!isset(static::$autoloader)) {
            if (!class_exists('Pi\\Application\\Autoloader', false)) {
                require static::PATH_LIB . '/Pi/Application/Autoloader.php';
            }
            static::$autoloader = new Pi\Application\Autoloader($options);
        }
        return static::$autoloader;
    }

    /**
     * Loads application engine
     *
     * @param string    $type       Application type
     * @param array     $config     Config data for the application
     * @return {@Pi\Application\Engine\AbstractEngine}
     */
    public static function engine($type = '', $config = array())
    {
        if (!isset(static::$engine)) {
            if (!$type) {
                $type = defined('APPLICATION_ENGINE') ? APPLICATION_ENGINE : static::DEFAULT_APPLICATION_ENGINE;
            }
            $appEngineClass = 'Pi\\Application\\Engine\\' . ucfirst($type);
            static::$engine = new $appEngineClass($config);
        }
        return static::$engine;
    }

    /**
     * Load a service by name or return service handler if name is not specified
     *
     * If service is not loaded with specified name, a service placeholder will be returned
     *
     * @param string    $name
     * @param array     $options
     * @return Pi\Application\Service\ServiceAbstract|Pi\Application\Service
     */
    public static function service($name = null, $options = array())
    {
        // service handler
        if (!isset(static::$service)) {
            static::$service = new Pi\Application\Service;
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
     * Load database identifier
     *
     * @return Pi\Application\Db
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
     * @return Pi\Application\Model\ModelAbstract
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
     * @return mixed    configuration value or config handler if $name is not specified
     */
    public static function config($name = null, $domain = null)
    {
        // config handler
        if (!isset(static::$config)) {
            static::$config = new Pi\Application\Config(static::path('config'));
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
     * Registry container for global variables
     *
     * @param string $index The location to store the value, if value is not set, to load the value.
     * @param mixed $value The object to store.
     * @return mixed
     */
    public static function registry($index, $value = null)
    {
        $index = strtolower($index);
        if (null !== $value) {
            static::$registry[$index] = $value;
        } else {
            return isset(static::$registry[$index]) ? static::$registry[$index] : null;
        }
    }

    /**
     * Register a shutdown callback with FILO
     *
     * @param string|array $callback Callback method to be called in shutdown
     * @param bool $toAppend To append current callback to registered shutdown list, false to prepend
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
    /**#@-*/

    /**#@+
     * Global APIs proxy to sub objects
     */
    /**
     * Convert a path to a physical one, proxy to host handler
     *
     * @param string    $url        Pi Engine path:
     *                                  with ':' or leading slash '/' - absolute path, do not convert
     *                                  First part as section, map to www if no section matched
     * @return string
     * @link Pi\Application\Host::path()
     */
    public static function path($url)
    {
        return static::$host->path($url);
    }

    /**
     * Convert a path to an URL, proxy to host handler
     *
     * @param string    $url        Pi Engine URI:
     *                                  With URI scheme "://" - absolute URI, do not convert
     *                                  First part as section, map to www if no section matched
     *                                  If section URI is relative, www URI will be appended
     * @param bool      $absolute   whether convert to full URI; relative URI is used by default, i.e. no hostname
     * @return string
     * @link Pi\Application\Host::url()
     */
    public static function url($url, $absolute = false)
    {
        return static::$host->url($url, $absolute);
    }

    /**
     * Logs audit information
     *
     * @param string $message
     * @param array|Traversable $extra
     */
    public static function log($message, $extra = array())
    {
        static::service('log')->audit($message, $extra);
    }
    /**#@-*/
}

// Initialize
Pi::init();
