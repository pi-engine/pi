<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Log;

/** @var int Production mode, no error display */
define('ERROR_REPORTING_PRODUCTION', 0);
/** @var int Development mode, all possible */
define('ERROR_REPORTING_DEVELOPMENT', -1);
/** @var int Debug/test mode, all errors except deprecated/notice messages */
define('ERROR_REPORTING_DEBUG',
    E_ALL & ~ (E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE));

/**
 * Custom error handler
 *
 * @link http://www.php.net/manual/en/function.set-error-handler.php
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ErrorHandler
{
    /**
     * Application error levels
     *
     * @var array
     */
    static protected $errorLevel = array(
        'production'    => ERROR_REPORTING_PRODUCTION,
        'development'   => ERROR_REPORTING_DEVELOPMENT,
        'debug'         => ERROR_REPORTING_DEBUG
    );

    /**
     * Error level map against Logger priority
     *
     * @var array
     */
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

    /** @var bool The handler is enabled */
    protected $active = true;

    /** @var Logger Error logger */
    protected $logger;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $errorReporting = static::$errorLevel['development'];
        if (isset($options['error_reporting'])) {
            $errorReporting = $options['error_reporting'];
        } elseif (isset($options['error_level'])
            && isset(static::$errorLevel[$options['error_level']])
        ) {
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
     * @param  Logger $logger
     * @return bool
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
     * Restore error handler
     *
     * @return void
     */
    public function unregister()
    {
        restore_error_handler();
    }

    /**
     * Set active
     *
     * @param bool|null $flag
     * @return self|bool
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

    /**
     * Log error information
     *
     * @param int       $errno
     * @param string    $errstr
     * @param string    $errfile
     * @param int       $errline
     * @param array     $errcontext
     * @return bool
     * @throws \Exception
     */
    public function handleError(
        $errno,
        $errstr = '',
        $errfile = '',
        $errline = 0,
        $errcontext = array()
    ) {
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

        return true;
    }
}
