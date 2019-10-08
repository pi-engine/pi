<?php

/**
 * ArangoDB PHP client: autoloader
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Handles automatic loading of missing class files.
 *
 * The autoloader can be nested with other autoloaders. It will only
 * process classes from its own namespace and ignore all others.<br>
 * <br>
 *
 * @package   ArangoDBClient
 * @since     0.2
 */
class Autoloader
{
    /**
     * Directory with library files
     *
     * @var string
     */
    private static $libDir;

    /**
     * Class file extension
     */
    const EXTENSION = '.php';

    /**
     * Initialise the autoloader
     *
     * @throws Exception
     * @return void
     */
    public static function init()
    {
        self::checkEnvironment();

        self::$libDir = __DIR__ . DIRECTORY_SEPARATOR;

        spl_autoload_register(__NAMESPACE__ . '\Autoloader::load');
    }

    /**
     * Handle loading of an unknown class
     *
     * This will only handle class from its own namespace and ignore all others.
     *
     * This allows multiple autoloaders to be used in a nested fashion.
     *
     * @param string $className - name of class to be loaded
     *
     * @return void
     */
    public static function load($className)
    {
        $namespace = __NAMESPACE__ . '\\';
        $length    = strlen($namespace);

        //        if (substr($className, 0, $length) !== $namespace) {
        if (0 !== strpos($className, $namespace)) {
            return;
        }

        // init() must have been called before
        assert(self::$libDir !== null);

        require self::$libDir . substr($className, $length) . self::EXTENSION;
    }

    /**
     * Check the runtime environment
     *
     * This will check whether the runtime environment is compatible with the
     * Arango PHP client.
     *
     * @throws ClientException
     * @return void
     */
    private static function checkEnvironment()
    {
        list($major, $minor) = explode('.', PHP_VERSION);

        if ((int) $major < 5 || ((int) $major === 5 && (int) $minor < 6)) {
            throw new ClientException('Incompatible PHP environment. Expecting PHP 5.6 or higher');
        }
    }
}

class_alias(Autoloader::class, '\triagens\ArangoDb\Autoloader');
