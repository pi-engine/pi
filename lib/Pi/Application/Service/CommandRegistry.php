<?php
/**
 * Smart image resizing (and manipulation) by url module for Zend Framework 2
 *
 * @link      http://github.com/tck/zf2-imageresizer for the canonical source repository
 * @copyright Copyright (c) 2014 Tobias Knab
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Pi\Application\Service;

use BadMethodCallException;

/**
 * self defined command registry
 *
 * @package TckImageResizer
 */
class CommandRegistry
{
    /**
     * singleton
     */
    private static $instance = null;

    /**
     * the actual commands
     *
     * @var array
     */
    protected $commands = [];

    /**
     * private constructor
     */
    private function __construct()
    {
    }

    /**
     * get singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * clear the singleton
     *
     * @return void
     */
    public static function destroy()
    {
        self::$instance = null;
    }

    /**
     * get command registry list
     *
     * @return array
     */
    public static function getCommands()
    {
        $instance = self::getInstance();

        return $instance->commands;
    }

    /**
     * command registry has command
     *
     * @param string $command
     *
     * @return boolean
     */
    public static function hasCommand($command)
    {
        if (!is_string($command) || strlen($command) < 1) {
            throw new BadMethodCallException('Parameter command is not a valid string');
        }
        $instance = self::getInstance();

        return isset($instance->commands[$command]);
    }

    /**
     * get command callback
     *
     * @param string $command
     *
     * @return array
     */
    public static function getCommand($command)
    {
        if (!is_string($command) || strlen($command) < 1) {
            throw new BadMethodCallException('Parameter command is not a valid string');
        }
        $instance = self::getInstance();

        if (!isset($instance->commands[$command])) {
            throw new BadMethodCallException('Command "' . $command . '" is not a registered command');
        }

        return $instance->commands[$command];
    }

    /**
     * register a image command
     *
     * @param  string $command
     * @param  callable $callback
     * @return self
     */
    public static function register($command, $callback)
    {
        if (!is_string($command) || strlen($command) < 1) {
            throw new BadMethodCallException('Parameter command is not a valid string');
        }
        if (!is_callable($callback)) {
            throw new BadMethodCallException('Parameter callback is not a valid callback');
        }

        $instance = self::getInstance();

        $instance->commands[$command] = $callback;

        return $instance;
    }
}
