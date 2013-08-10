<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Log\Writer;

use Pi;
use Pi\Log\Logger;
use Pi\Log\Formatter\Syslog as SyslogFormatter;
use Zend\Log\Writer\AbstractWriter;
use Zend\Log\Formatter\FormatterInterface;

/**
 * Syslog writer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Syslog extends AbstractWriter
{
    /**
     * Maps Pi\Log\Logger priorities to PHP's Syslog priorities
     * @var array
     */
    protected $priorities = array(
        Logger::EMERG  => LOG_EMERG,
        Logger::ALERT  => LOG_ALERT,
        Logger::CRIT   => LOG_CRIT,
        Logger::ERR    => LOG_ERR,
        Logger::WARN   => LOG_WARNING,
        Logger::NOTICE => LOG_NOTICE,
        Logger::INFO   => LOG_INFO,
        Logger::DEBUG  => LOG_DEBUG,
    );

    /**
     * The default log priority - for unmapped custom priorities
     * @var int
     */
    protected $defaultPriority = LOG_NOTICE;

    /**
     * Last application name set by a syslog-writer instance
     * @var string
     */
    protected static $lastApplication;

    /**
     * Last facility name set by a syslog-writer instance
     * @var string
     */
    protected static $lastFacility;

    /**
     * Application name used by this syslog-writer instance
     * @var string
     */
    protected $appName = 'Pi\Log';

    /**
     * Facility used by this syslog-writer instance
     * @var int
     */
    protected $facility = LOG_USER;

    /**
     * Types of program available to logging of message
     * @var array
     */
    protected $validFacilities = array();

    /**
     * Constructor
     *
     * @param array $params
     *      Array of options; may include "application" and "facility" keys
     */
    public function __construct(array $params = array())
    {
        if (isset($params['application'])) {
            $this->application = $params['application'];
        }

        $runInitializeSyslog = true;
        if (isset($params['facility'])) {
            $this->setFacility($params['facility']);
            $runInitializeSyslog = false;
        }

        if ($runInitializeSyslog) {
            $this->initializeSyslog();
        }
    }

    /**
     * Initialize values facilities
     *
     * @return void
     */
    protected function initializeValidFacilities()
    {
        $constants = array(
            'LOG_AUTH',
            'LOG_AUTHPRIV',
            'LOG_CRON',
            'LOG_DAEMON',
            'LOG_KERN',
            'LOG_LOCAL0',
            'LOG_LOCAL1',
            'LOG_LOCAL2',
            'LOG_LOCAL3',
            'LOG_LOCAL4',
            'LOG_LOCAL5',
            'LOG_LOCAL6',
            'LOG_LOCAL7',
            'LOG_LPR',
            'LOG_MAIL',
            'LOG_NEWS',
            'LOG_SYSLOG',
            'LOG_USER',
            'LOG_UUCP'
        );

        foreach ($constants as $constant) {
            if (defined($constant)) {
                $this->validFacilities[] = constant($constant);
            }
        }
    }

    /**
     * Initialize syslog / set application name and facility
     *
     * @return void
     */
    protected function initializeSyslog()
    {
        static::$lastApplication = $this->appName;
        static::$lastFacility    = $this->facility;
        openlog($this->appName, LOG_PID, $this->facility);
    }

    /**
     * Set syslog facility
     *
     * @param int $facility Syslog facility
     * @return self
     * @throws \InvalidArgumentException for invalid log facility
     */
    public function setFacility($facility)
    {
        if ($this->facility === $facility) {
            return $this;
        }

        if (!count($this->validFacilities)) {
            $this->initializeValidFacilities();
        }

        if (!in_array($facility, $this->validFacilities)) {
            throw new \InvalidArgumentException(
            	'Invalid log facility provided;'
                . ' please see http://php.net/openlog'
                . ' for a list of valid facility values'
            );
        }

        if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))
            && ($facility !== LOG_USER)
        ) {
            throw new \InvalidArgumentException(
                'Only LOG_USER is a valid log facility on Windows'
            );
        }

        $this->facility = $facility;
        $this->initializeSyslog();

        return $this;
    }

    /**
     * Set application name
     *
     * @param string $appName Application name
     * @return self
     */
    public function setApplicationName($appName)
    {
        if ($this->appName === $appName) {
            return $this;
        }

        $this->appName = $appName;
        $this->initializeSyslog();

        return $this;
    }

    /**
     * Close syslog
     *
     * @return void
     */
    public function shutdown()
    {
        closelog();
    }

    /**
     * Write a message to syslog.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        if (array_key_exists($event['priority'], $this->priorities)) {
            $priority = $this->priorities[$event['priority']];
        } else {
            $priority = $this->defaultPriority;
        }

        if ($priority > Logger::DEBUG) {
            return;
        }

        if ($this->appName !== static::$lastApplication
            || $this->facility !== static::$lastFacility
        ) {
            $this->initializeSyslog();
        }

        if ($this->formatter instanceof FormatterInterface) {
            $message = $this->formatter->format($event);
        } else {
            $message = $event['message'];
        }

        syslog($priority, $message);
    }
}
