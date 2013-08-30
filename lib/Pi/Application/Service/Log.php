<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Log\Logger;
use Pi\Log\ErrorHandler;
use Pi\Log\ExceptionHandler;
use Pi\Log\Profiler;
use Pi\Log\DbProfiler;
use Pi\Log\Writer\Debugger;

/**
 * Logging service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Log extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    protected $fileIdentifier = 'log';

    /**
     * Whether or not to the service is active
     *
     * @var bool
     */
    protected $active;

    /** @var Debugger Debugger writer */
    protected $debugger;

    /** @var Logger Log handler */
    protected $logger;

    /** @var ErrorHandler Error handler */
    protected $errorHandler;

    /** @var ExceptionHandler Exception handler */
    protected $exceptionHandler;

    /** @var Profiler Profiler writer */
    protected $profiler;

    /** @var DbProfiler DbProfiler writer */
    protected $dbProfiler;

    /**
     * Log service constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        // Active
        if (isset($this->options['active'])) {
            $this->active = (bool) $this->options['active'];
        }
        // Set logger
        $this->logger = $this->logger();
        // Debugger
        if (isset($this->options['debugger'])) {
            $this->debugger();
        }
        // Register error handler
        if (isset($this->options['error_handler'])) {
            $this->registerErrorHandler($this->options['error_handler']);
        }
        // Register exception handler
        if (isset($this->options['exception_handler'])) {
            $this->registerExceptionHandler(
                $this->options['exception_handler']
            );
        }
        // Set profiler
        if (isset($this->options['profiler'])) {
            $this->profiler();
        }
        // set Db profiler
        if (isset($this->options['db_profiler'])) {
            $this->dbProfiler();
        }
    }

    /**
     * Shutdown function, will be triggered by Pi::shutdown()
     *
     * @return void
     */
    public function shutdown()
    {
        if (!$this->active()) {
            return;
        }
        // Write profiling data
        if ($this->profiler) {
            $this->profiler->shutdown();
        }

        // DB query profiling data
        if ($this->dbProfiler) {
            $this->dbProfiler->shutdown();
        }

        // Debug and audit information
        $this->logger->shutdown();

        // Debugger output
        if ($this->debugger) {
            $this->debugger->render();
        }
    }

    /**
     * Enable/disable or get activation
     *
     * @param bool|null $flag
     * @return bool
     */
    public function active($flag = null)
    {
        if (null !== $flag) {
            $this->active = (bool) $flag;
            if ($this->errorHandler) {
                $this->errorHandler->active($this->active);
            }
            if ($this->exceptionHandler) {
                $this->exceptionHandler->active($this->active);
            }
        } elseif (null === $this->active) {
            if (!empty($this->options['ip'])) {
                $this->active = (bool) Pi::service('security')->ip(array(
                    'good' => $this->options['ip'],
                ));
            } else {
                $this->active = true;
            }
        }

        return $this->active;
    }

    /**
     * Enable/disable debugger
     *
     * @param bool $flag
     * @return bool|null Return previous muted value
     *      or null if no debugger available
     */
    public function mute($flag = true)
    {
        $muted = null;
        if ($this->debugger) {
            $muted = $this->debugger->mute($flag);
        }

        return $muted;
    }

    /**
     * Get logger, instantiate it if not available
     *
     * @param Logger $logger
     * @return Log|Logger
     */
    public function logger(Logger $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
            return $this;
        }
        if (!$this->logger) {
            $options = $this->options['logger'];
            if (!isset($options['active'])) {
                $options['active'] = $this->active();
            }
            $this->logger = new Logger($options);
        }

        return $this->logger;
    }

    /**
     * Get debugger writer, instantiate it if not available
     *
     * @param Debugger $debugger
     * @return Log|Debugger
     */
    public function debugger(Debugger $debugger = null)
    {
        if (null !== $debugger) {
            $this->debugger = $debugger;
            return $this;
        }
        if (null === $this->debugger && isset($this->options['debugger'])) {
            if (!isset($this->options['debugger']['active'])
                || false !== $this->options['debugger']['active']
            ) {
                $this->debugger = $this->logger()->writerPlugin(
                    'debugger',
                    $this->options['debugger']
                );
                $this->logger()->addWriter($this->debugger);
            } else {
                $this->debugger = false;
            }
        }

        return $this->debugger;
    }

    /**
     * Get profiler handler, instantiate it if not available
     *
     * @param Profiler $profiler
     * @return Log|Profiler
     */
    public function profiler(Profiler $profiler = null)
    {
        if (null !== $profiler) {
            $this->profiler = $profiler;
            return $this;
        }
        if (null === $this->profiler && isset($this->options['profiler'])) {
            if (!isset($this->options['profiler']['active'])
                || false !== $this->options['profiler']['active']
            ) {
                $this->profiler = new Profiler($this->options['profiler']);
                if ($this->debugger()) {
                    $this->profiler->addWriter($this->debugger());
                }
                $this->profiler->start();
            } else {
                $this->profiler = false;
            }
        }

        return $this->profiler;
    }

    /**
     * Get DB query profiler, instantiate it if not available
     *
     * @param DbProfiler $dbProfiler
     * @return Log|DbProfiler
     */
    public function dbProfiler(DbProfiler $dbProfiler = null)
    {
        if (null !== $dbProfiler) {
            $this->dbProfiler = $dbProfiler;
            return $this;
        }
        if (null === $this->dbProfiler
            && isset($this->options['db_profiler'])
        ) {
            if (!isset($this->options['db_profiler']['active'])
                || false !== $this->options['db_profiler']['active']
            ) {
                $this->dbProfiler = new DbProfiler(
                    $this->options['db_profiler']
                );
                if ($this->debugger()) {
                    $this->dbProfiler->addWriter($this->debugger());
                }
            } else {
                $this->dbProfiler = false;
            }
        }

        return $this->dbProfiler;
    }

    /**
     * Register custom error handler
     *
     * @param array $options
     * @return Log
     */
    public function registerErrorHandler($options)
    {
        $this->errorHandler = new ErrorHandler($options);
        $this->errorHandler->register($this->logger());

        return $this;
    }

    /**
     * Register custom exception handler
     *
     * @param array $options
     * @return Log
     */
    public function registerExceptionHandler($options)
    {
        $this->exceptionHandler = new ExceptionHandler($options);
        $this->exceptionHandler->register($this->logger());

        return $this;
    }

    /**
     * Log DB query profiling info
     *
     * @param array $info
     * @return Log
     */
    public function db($info)
    {
        if (!$this->active()) {
            return $this;
        }
        $this->dbProfiler() ? $this->dbProfiler()->log($info) : null;

        return $this;
    }

    /**
     * Start a profiler
     *
     * @param string $name
     * @return Log
     */
    public function start($name = 'Pi Engine')
    {
        if (!$this->active()) {
            return $this;
        }
        $this->profiler() ? $this->profiler()->start($name) : null;

        return $this;
    }

    /**
     * End a profiler
     *
     * @param string $name
     * @return Log
     */
    public function end($name = 'Pi Engine')
    {
        if (!$this->active()) {
            return $this;
        }
        $this->profiler() ? $this->profiler()->end($name) : null;

        return $this;
    }

    /**
     * Undefined method handler allows a shortcut:
     *   `$log->err('message')`
     * or
     *   `$log->log('message', 'err')`
     *
     * @param  string $method  priority name
     * @param  array  $args
     *
     * @return void
     */
    public function __call($method, $args)
    {
        if (!$this->active()) {
            return $this;
        }
        if (method_exists($this->logger, $method)) {
            call_user_func_array(array($this->logger, $method), $args);
        }

        return $this;
    }
}
