<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Pi Engine
 *
 * System bootstrap and global interfaces to applications and modules
 *
 * Boot up process:
 *
 * - init: instantiate and initialize gobal APIs
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
 *   - bootstrap()
 *   - application()
 *
 * - bootstrap: boostap application
 *
 *   - bootstrap(application)
 *
 * - run: run application and returns response
 *
 *   - run(): route, dispatch
 *
 * - send response
 *
 *   - send()
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
 *  - registry()
 *  - service()
 *  - url()
 *  - user()
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
     * @var Pi\Application\Host
     */
    protected static $host = null;

    /**
     * Reference to persist handler
     * @var Pi\Application\Persist
     */
    protected static $persist = null;

    /**
     * Reference to autoloader handler
     * @var Pi\Application\Autoloader
     */
    protected static $autoloader = null;

    /**
     * Reference to application engine
     * @var array of Pi\Application\Engine
     */
    protected static $engine = null;

    /**
     * Reference to service handler
     * @var Pi\Application\Service
     */
    protected static $service = null;

    /**
     * Reference to config handler
     * @var Pi\Application\Config
     */
    protected static $config = null;

    /**
     * Reference to Db handler
     * @var Pi\Application\Db
     */
    protected static $db = null;

    /**
     * Registry container
     * @var array
     */
    protected static $registry = array();

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
        // Set default timezone if not available in php.ini
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('UTC');
        }

        // Set start time
        static::$start = microtime(true);

        /**#@+
         * Initialize Host
         */
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
        /**#@-*/

        /**#@+
         * Register autoloader, host, persist and autoloader
         */
        $paths = static::host()->get('path');
        $options = array(
            // Top namespaces
            'top'       => array(
                'Pi'    => static::path('lib') . '/Pi',
                'Zend'  => static::path('lib') . '/Zend',
            ),
            // Regular namespaces
            'namespace' => array(
            ),
            // Class map
            'class_map'  => array(
            ),
            // Directory of modules
            'module_path'    => static::path('module'),
            // Directory of extras
            'extra_path'    => !empty($paths['extra'])
                ? $paths['extra'] : static::path('usr') . '/extra',
            // Vendor directory
            'include_path'   => !empty($paths['vendor'])
                ? $paths['vendor'] : static::path('lib') . '/vendor',
        );
        static::autoloader($options);
        /**#@-*/

        // Load debugger and filter
        Pi\Debug\Debug::load();
        Pi\Utility\Filter::load();

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
        $persistConfig = empty($engineConfig['persist'])
            ? array() : $engineConfig['persist'];
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
     * @param string|array $config Host file path or array of config data
     * @return Pi\Application\Host
     */
    public static function host($config = null)
    {
        if (!isset(static::$host)) {
            if (!class_exists('Pi\Application\Host', false)) {
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
     * @return Pi\Application\Persist\PersistInterface
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
     * @return Pi\Application\Engine\AbstractEngine
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
     * Load user service
     *
     * @return Pi\Application\Service\User
     */
    public static function user()
    {
        return static::service('user');
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
     * @return mixed    config value or config handler if $name not specified
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
     * Register a variable to global container, or fetch a glbal registry if
     * variable value is not provided
     *
     * @param string    $index  Name of the value
     * @param mixed     $value  The value to store.
     * @return mixed
     */
    public static function registry($index, $value = null)
    {
        $index = strtolower($index);
        if (null !== $value) {
            static::$registry[$index] = $value;
        } else {
            return isset(static::$registry[$index])
                ? static::$registry[$index] : null;
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
    /**#@-*/

    /**#@+
     * Global APIs proxy to sub objects
     */
    /**
     * Convert a path to a physical one, proxy to host handler
     *
     * For path value to be examined:
     *
     *  - With `:` or leading slash `/` - absolute path, do not convert;
     *  - Otherwise, first part as section, map to `www` if no section matched
     *
     * @see \Pi\Application\Host::path()
     * @param string $url  Path to be converted
     * @return string
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
     * @see Pi\Application\Host::url()
     * @param string    $url        URL to be converted
     * @param bool      $absolute
     *  Convert to full URI; Default as relative URI with no hostname
     * @return string
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
     */
    public static function log($message, $extra = array())
    {
        static::service('log')->audit($message, $extra);
    }
    /**#@-*/
}

/**
 * Initialize Pi Engine by calling `Pi::init()`
 */
Pi::init();
