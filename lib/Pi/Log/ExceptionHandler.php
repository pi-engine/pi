<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Log;

/**
 * Custom exception handler
 *
 * @link   http://www.php.net/manual/en/function.set-exception-handler.php
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ExceptionHandler
{
    /** @var bool The handler is enabled */
    protected $active = true;

    /** @var Logger Exception logger */
    protected $logger;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        // Active
        if (isset($options['active'])) {
            $this->active = (bool)$options['active'];
        }
        // Disable error handling if xdebug is enabled
        if (extension_loaded('xdebug')) {
            $this->active = false;
        }

        return true;
    }

    /**
     * Set active
     *
     * @param bool|null $flag
     *
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
     * Register logging system as an exception handler to log PHP exceptions
     *
     * @param Logger $logger
     *
     * @return bool
     */
    public function register(Logger $logger = null)
    {
        $this->logger = $logger ?: $this->logger;
        if (!$this->logger || !$this->active) {
            return false;
        }
        $logger = $this->logger;

        set_exception_handler(
            function ($exception) use ($logger) {
                $extra = [
                    'file'  => $exception->getFile(),
                    'line'  => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                    'time'  => microtime(true),
                ];
                if (isset($exception->xdebug_message)) {
                    $extra['xdebug'] = $exception->xdebug_message;
                }
                $logger->log(Logger::ERR, $exception->getMessage(), $extra);
            }
        );

        return true;
    }

    /**
     * Restore exception handler
     *
     * @return void
     */
    public function unregister()
    {
        restore_exception_handler();
    }
}
