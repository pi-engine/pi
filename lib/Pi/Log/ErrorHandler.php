<?php
/**
 * Pi Error Handler
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
 * @package         Pi\Log
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Log;

define('ERROR_REPORTING_PRODUCTION', 0);    // Production mode, no error display
define('ERROR_REPORTING_DEVELOPMENT', -1);  // Development mode, all possible
define('ERROR_REPORTING_DEBUG', E_ALL & ~ (E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE));   // Debug/test mode, all errors except deprecated/notice messages

class ErrorHandler
{
    static protected $errorLevel = array(
        'production'    => ERROR_REPORTING_PRODUCTION,
        'development'   => ERROR_REPORTING_DEVELOPMENT,
        'debug'         => ERROR_REPORTING_DEBUG
    );
    protected $errorHandlerMap = array(
        E_NOTICE            => Logger::NOTICE,
        E_USER_NOTICE       => Logger::NOTICE,
        E_WARNING           => Logger::WARN,
        E_CORE_WARNING      => Logger::WARN,
        E_USER_WARNING      => Logger::WARN,
        E_ERROR             => Logger::ERR,
        E_USER_ERROR        => Logger::ERR,
        E_CORE_ERROR        => Logger::ERR,
        E_RECOVERABLE_ERROR => Logger::ERR,
        E_STRICT            => Logger::DEBUG,
        E_DEPRECATED        => Logger::DEBUG,
        E_USER_DEPRECATED   => Logger::DEBUG
    );

    protected $active = true;
    protected $logger;

    /**
     * Initializes this instance
     */
    public function __construct($options = array())
    {
        $errorReporting = static::$errorLevel['development'];
        if (isset($options['error_reporting'])) {
            $errorReporting = $options['error_reporting'];
        } elseif (isset($options['error_level']) && isset(static::$errorLevel[$options['error_level']])) {
            $errorReporting = static::$errorLevel[$options['error_level']];
        }
        $this->errorReporting = $errorReporting;
        // Active
        if (isset($options['active'])) {
            $this->active = (bool) $options['active'];
        }
        /*
        // Disable error handling if xdebug is enabled
        if (extension_loaded('xdebug')) {
            $this->active = false;
        }
        */
        return true;
    }

    /**
     * Register logging system as an error handler to log PHP errors
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php
     *
     * @param  Logger $logger
     * @return boolean
     */
    public function register(Logger $logger = null)
    {
        $this->logger = $logger ?: $this->logger;
        if (!$this->logger || !$this->active) {
            return false;
        }
        set_error_handler(array($this, 'handleError'));

        return true;
    }

    /**
     * Unregister error handler
     *
     */
    public function unregister()
    {
        restore_error_handler();
    }

    /**
     * Set activity
     * @param bool|null $flag
     * @return ErrorHandler
     */
    public function active($flag = null)
    {
        if (null === $flag) {
            return $this->active;
        }
        if ($flag === $this->active) {
            return $this;
        }
        if (true === $flag) {
            $this->active = true;
            $this->register();
        } else {
            $this->active = false;
            $this->unregister();
        }
        return $this;
    }

    public function handleError($errno, $errstr = '', $errfile = '', $errline = 0, $errcontext = array())
    {
        if ($this->errorReporting & $errno) {
            if (isset($this->errorHandlerMap[$errno])) {
                $priority = $this->errorHandlerMap[$errno];
            } else {
                $priority = Logger::INFO;
            }
            try {
                $this->logger->log($priority, $errstr, array(
                    'errno'     => $errno,
                    'file'      => $errfile,
                    'line'      => $errline,
                    'context'   => $errcontext,
                    'time'      => microtime(true)
                ));
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return;
    }
}
