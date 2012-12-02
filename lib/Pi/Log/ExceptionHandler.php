<?php
/**
 * Pi Exception Handler
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

class ExceptionHandler
{
    protected $active = true;
    protected $logger;

    /**
     * Initializes this instance
     */
    public function __construct($options = array())
    {
        // Active
        if (isset($options['active'])) {
            $this->active = (bool) $options['active'];
        }
        // Disable error handling if xdebug is enabled
        if (extension_loaded('xdebug')) {
            $this->active = false;
        }
        return true;
    }

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
     * @link http://www.php.net/manual/en/function.set-exception-handler.php
     *
     * @param Logger $logger
     * @return type
     */
    public function register(Logger $logger = null)
    {
        $this->logger = $logger ?: $this->logger;
        if (!$this->logger || !$this->active) {
            return false;
        }
        $logger = $this->logger;

        set_exception_handler(function ($exception) use ($logger){
            $extra = array (
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTrace(),
                'time'  => microtime(true),
            );
            if (isset($exception->xdebug_message)) {
                $extra['xdebug'] = $exception->xdebug_message;
            }
            $logger->log(Logger::ERR, $exception->getMessage(), $extra);
        });
        return true;
    }

    /**
     * Unregister exception handler
     */
    public function unregister()
    {
        restore_exception_handler();
    }
}
