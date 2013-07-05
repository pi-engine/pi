<?php
/**
 * Pi Autoloader
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
 * @package         Pi\Application
 */

/**
 * Autoloader handler
 *
 * Options are loaded in Pi::init()
 *
 * Autoloading priority:
 * 1. class map
 * 2. PSR standard
 *    2.1 module namespace
 *    2.2 Pi and Zend namespace
 *    2.3 registered namespace
 *    2.4 vendor namespace
 * 3. fallbacks
 *    3.1 custom autoloader
 */

namespace Pi\Application;

class Autoloader
{
    /**
     * @var constant Top namespace for modules
     */
    const TOP_NAMESPACE_MODULE = 'Module';

    /**
     * @var constant Directory for module source code. Module classes are located in /usr/module/modulename/src/
     *
     */
    const MODULE_SOURCE_DIRECTORY = 'src';

    /**
     * Namespace speparator
     */
    const NS_SEPARATOR     = '\\';

    /**
     * @var array Top namespace/directory pairs to match; Pi, Zend added by default
     */
    protected $tops = array();

    /**
     * @var array Callbacks to locate class file
     */
    protected $callbacks = array();

    /**
     * Persist handler
     * @var string
     */
    protected $persist;

    /**
     * Directory of modules
     * @var string
     */
    protected $modulePath = '';

    /**#@+
     * Factory variables
     */
    /**
     * @var array All autoloaders registered
     */
    protected $loaders = array();
    /**#@-*/

    /**#@+
     * Class-map autoloader variables
     */
    /**
     * Registry of map files that have already been loaded
     * @var array
     */
    protected $mapsLoaded = array();

    /**
     * Class name/filename map
     * @var array
     */
    protected $map = array();
    /**#@-*/

    /**
     * @var array Namespace/directory pairs to search; ZF library added by default
     */
    protected $namespaces = array();

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     *          includepath - path to set for vendors
     *          modulepath  - path to modules
     *          top         - paths to top namespaces
     *          namespace   - paths to regular namespaces
     *          classmap    - class-path map
     * @return void
     */
    public function __construct($options = array())
    {
        // Include paths, adding vendor path
        if (!empty($options['includepath'])) {
            set_include_path(get_include_path() . \PATH_SEPARATOR . $options['includepath']);
        }
        // Module directory
        if (!empty($options['modulepath'])) {
            $this->modulePath = $options['modulepath'];
        }
        // class map
        if (!empty($options['classmap'])) {
            $this->registerAutoloadMap($options['classmap']);
        }
        // namespaces
        if (!empty($options['top'])) {
            $this->registerTops($options['top']);
        }
        // namespaces
        if (!empty($options['namespace'])) {
            $this->registerNamespaces($options['namespace']);
        }
        $this->register();
    }

    /**
     * Set persist handler for class/file map
     *
     * @return Autoloader
     */
    public function setPersist(Persist\PersistInterface $persist)
    {
        $this->persist = $persist;
        return $this;
    }

    /**
     * Register the autoloader with spl_autoload registry
     *
     * @return void
     */
    public function register()
    {
        // Register class map autoloader
        spl_autoload_register(array($this, 'autoloadMap'));

        // Register persist class map autoloader
        //if ($this->persist) {
            spl_autoload_register(array($this, 'autoloadPersist'));
        //}

        // Register PSR rule map autoloader
        spl_autoload_register(array($this, 'autoloadStandard'));
    }

    /**
     * Load by class map
     *
     * @param  string $class
     * @return void
     */
    public function autoloadMap($class)
    {
        if (isset($this->map[$class])) {
            require_once $this->map[$class];
        }
    }

    /**
     * Load by persist class map which is registered in standard autoloader or custom autoloader
     *
     * @param  string $class
     * @return void
     */
    public function autoloadPersist($class)
    {
        if (!$this->persist) {
            return;
        }
        $path = $this->persist->load($class);
        // If class is registered in persist and valid
        if (!empty($path)) {
            if (!include $path) {
                trigger_error(sprintf('Class "%s" is not loaded from "%s"', $class, $path));
            }
        }
    }

    /**
     * Load by PSR standard autoloader
     *
     * Autoloading order:
     *  1. Top namespaces: Pi, Zend, ...
     *  2. Zend namespace
     *  3. registered namespace with specified path
     *  4. vendor namespaces located in include paths
     *
     * @param   string $class
     * @return  void
     */
    public function autoloadStandard($class)
    {
        if (false === ($pos = strpos($class, static::NS_SEPARATOR))) {
            return;
        }
        $filePath = false;

        /**#@+
         * Check in top namespaces
         */
        $top = substr($class, 0, $pos);
        // Module classes, Module\ModuleName\ClassNamespace\ClassName
        if (static::TOP_NAMESPACE_MODULE === $top) {
            list($top, $module, $trimmedClass) = explode(static::NS_SEPARATOR, $class, 3);
            $path = $this->modulePath . \DIRECTORY_SEPARATOR . strtolower($module) . \DIRECTORY_SEPARATOR . static::MODULE_SOURCE_DIRECTORY . \DIRECTORY_SEPARATOR;
            $filePath = $this->transformClassNameToFilename($trimmedClass, $path);

        // Top namespaces
        } elseif (!empty($this->tops[$top])) {
            // Trim off leader
            $trimmedClass = substr($class, strlen($top . static::NS_SEPARATOR));
            $path = $this->tops[$top];
            // Get file full path
            $filePath = $this->transformClassNameToFilename($trimmedClass, $path);
        /*#@-*/

        } else {
            // Lookup in regular namespaces
            foreach ($this->namespaces as $leader => $path) {
                if (0 === strpos($class, $leader)) {
                    // Trim off leader
                    $trimmedClass = substr($class, strlen($leader));
                    // Get file full path
                    $filePath = $this->transformClassNameToFilename($trimmedClass, $path);
                    // Break
                    break;
                }
            }
            // Lookup in included paths
            if (false === $filePath) {
                $fileName = $this->transformClassNameToFilename($class, '');
                $filePath = stream_resolve_include_path($fileName);
            }
            // Lookup via custom callbacks
            if (false === $filePath) {
                foreach ($this->callbacks as $callback) {
                    $filePath = call_user_func($callback, $class);
                    if (false !== $filePath && null !== $filePath) {
                        break;
                    }
                }
            }
        }

        // Load class file if found
        if (false !== $filePath) {
            if ($this->persist) {
                $this->persist->save($class, $filePath);
            }
            if (file_exists($filePath)) {
                return include $filePath;
            }
        }
    }

    /**
     * Register a custom callback to locate class file
     *
     * @param array|string  $callback array of (class, method) or function
     * @param bool          $append  append or prepend to callback list
     * @return Autoloader
     */
    public function registerCallback($callback, $append = true)
    {
        if ($append) {
            $this->callbacks[] = $callback;
        } else {
            array_unshift($this->callbacks, $callback);
        }
        return $this;
    }

    /**
     * Register multiple top namespace/directory pairs at once
     *
     * @param  array $namespaces
     * @return Autoloader
     */
    public function registerTops($namespaces)
    {
        if (!is_array($namespaces) && !$namespaces instanceof \Traversable) {
            throw new \InvalidArgumentException('Namespace pairs must be either an array or Traversable');
        }

        foreach ($namespaces as $namespace => $directory) {
            $this->registerTop($namespace, $directory);
        }
        return $this;
    }

    /**
     * Register a top-namespace/directory pair
     *
     * @param  string $namespace
     * @param  string $directory
     * @return Autoloader
     */
    public function registerTop($namespace, $directory)
    {
        $this->tops[$namespace] = $this->normalizeDirectory($directory);
        return $this;
    }

    /**
     * Transform the class name to a filename following PSR standard
     *
     * @param  string $class
     * @param  string $directory
     * @return string
     */
    protected function transformClassNameToFilename($class, $directory)
    {
        return $directory
            . str_replace(
                static::NS_SEPARATOR,
                \DIRECTORY_SEPARATOR,
                $class
            )
            . '.php';
    }

    /**#@+
     * Factory methods
     */
    /**
     * Factory for autoloaders
     *
     * Options should be an array or Traversable object of the following structure:
     * <code>
     * array(
     *     '<autoloader class name>' => $autoloaderOptions,
     * )
     * </code>
     *
     * The factory will then loop through and instantiate each autoloader with
     * the specified options, and register each with the spl_autoloader.
     *
     * You may retrieve the concrete autoloader instances later using
     * {@link getRegisteredAutoloaders()}.
     *
     * Note that the class names must be resolvable on the include_path or via
     * the Zend library, using PSR-0 rules (unless the class has already been
     * loaded).
     *
     * @param  array|Traversable $options options to use
     * @return void
     * @throws \InvalidArgumentException for invalid options
     * @throws \InvalidArgumentException for unloadable autoloader classes
     */
    public function factory($options = array())
    {
        if (!is_array($options) && !($options instanceof \Traversable)) {
            throw new \InvalidArgumentException(
                             'Options provided must be an array or Traversable'
            );
        }

        foreach ($options as $class => $options) {
            // set persist handler for each handler
            $options['persist'] = $this->persist;

            if (!isset($this->loaders[$class])) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(
                                sprintf('Autoloader class "%s" not loaded', $class)
                    );
                }
                // Instantiate autoloader
                $autoloader = new $class($options);
                // Register autoloader
                $autoloader->register();
                $this->loaders[$class] = $autoloader;
            } else {
                $this->loaders[$class]->setOptions($options);
            }
        }
    }
    /*#@-*/

    /**#@+
     * class-map methods
     */
    /**
     * Register an autoload map
     *
     * An autoload map may be either an associative array, or a file returning
     * an associative array.
     *
     * An autoload map should be an associative array containing
     * classname/file pairs.
     *
     * @param  string|array $location
     * @return Autoloader
     */
    public function registerAutoloadMap($map)
    {
        if (is_string($map)) {
            $location = $map;
            if ($this === ($map = $this->loadMapFromFile($location))) {
                return $this;
            }
        }

        if (!is_array($map)) {
            throw new \InvalidArgumentException('Map file provided does not return a map');
        }

        $this->map = array_merge($this->map, $map);

        if (isset($location)) {
            $this->mapsLoaded[] = $location;
        }

        return $this;
    }

    /**
     * Register many autoload maps at once
     *
     * @param  array $locations
     * @return Autoloader
     */
    public function registerAutoloadMaps($locations)
    {
        if (!is_array($locations) && !($locations instanceof \Traversable)) {
            throw new \InvalidArgumentException('Map list must be an array or implement Traversable');
        }
        foreach ($locations as $location) {
            $this->registerAutoloadMap($location);
        }
        return $this;
    }

    /**
     * Retrieve current autoload map
     *
     * @return array
     */
    public function getAutoloadMap()
    {
        return $this->map;
    }

    /**
     * Load a map from a file
     *
     * If the map has been previously loaded, returns the current instance;
     * otherwise, returns whatever was returned by calling include() on the
     * location.
     *
     * @param  string $location
     * @return Autoloader|mixed
     * @throws \InvalidArgumentException for nonexistent locations
     */
    protected function loadMapFromFile($location)
    {
        if (!file_exists($location)) {
            throw new \InvalidArgumentException('Map file provided does not exist');
        }

        if (!$path = static::realPharPath($location)) {
            $path = realpath($location);
        }

        if (in_array($path, $this->mapsLoaded)) {
            // Already loaded this map
            return $this;
        }

        $map = include $path;

        return $map;
    }

    /**
     * Resolve the real_path() to a file within a phar.
     *
     * @see https://bugs.php.net/bug.php?id=52769
     * @param string $path
     * @return string
     */
    public static function realPharPath($path)
    {
        if (strpos($path, 'phar:///') !== 0) {
            return;
        }

        $parts = explode('/', str_replace(array('/','\\'), '/', substr($path, 8)));
        $parts = array_values(array_filter($parts, function($p) { return ($p !== '' && $p !== '.'); }));

        array_walk($parts, function ($value, $key) use(&$parts) {
            if ($value === '..') {
                unset($parts[$key], $parts[$key-1]);
                $parts = array_values($parts);
            }
        });

        if (file_exists($realPath = 'phar:///' . implode('/', $parts))) {
            return $realPath;
        }
    }
    /*#@-*/

    /**#@+
     * PSR-0 compliant autoloader methods
     */
    /**
     * Register a namespace/directory pair
     *
     * @param  string $namespace
     * @param  string $directory
     * @return Autoloader
     */
    public function registerNamespace($namespace, $directory)
    {
        $namespace = $namespace . static::NS_SEPARATOR;
        $this->namespaces[$namespace] = $this->normalizeDirectory($directory);
        return $this;
    }

    /**
     * Register many namespace/directory pairs at once
     *
     * @param  array $namespaces
     * @return Autoloader
     */
    public function registerNamespaces($namespaces)
    {
        if (!is_array($namespaces) && !$namespaces instanceof \Traversable) {
            throw new \InvalidArgumentException('Namespace pairs must be either an array or Traversable');
        }

        foreach ($namespaces as $namespace => $directory) {
            $this->registerNamespace($namespace, $directory);
        }
        return $this;
    }

    /**
     * Normalize the directory to include a trailing directory separator
     *
     * @param  string $directory
     * @return string
     */
    protected function normalizeDirectory($directory)
    {
        $last = $directory[strlen($directory) - 1];
        if (in_array($last, array('/', '\\'))) {
            $directory[strlen($directory) - 1] = \DIRECTORY_SEPARATOR;
            return $directory;
        }
        $directory .= \DIRECTORY_SEPARATOR;
        return $directory;
    }
    /*#@-*/
}

interface SplAutoloader
{
    /**
     * Constructor
     *
     * Allow configuration of the autoloader via the constructor.
     *
     * @param  null|array|Traversable $options
     * @return void
     */
    public function __construct($options = null);

    /**
     * Configure the autoloader
     *
     * In most cases, $options should be either an associative array or
     * Traversable object.
     *
     * @param  array|Traversable $options
     * @return SplAutoloader
     */
    public function setOptions($options);

    /**
     * Register the autoloader with spl_autoload registry
     *
     * Typically, the body of this will simply be:
     * <code>
     * spl_autoload_register(array($this, 'autoload'));
     * </code>
     *
     * @return void
     */
    //public function register();
    public function register($throw = true, $prepend = false);
}
