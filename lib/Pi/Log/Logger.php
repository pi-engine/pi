<?php
/**
 * Pi Logger
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

use Zend\Stdlib\SplPriorityQueue;
use Traversable;
use Zend\Log\Writer;
use Zend\Stdlib\ArrayUtils;

class Logger
{
    /**
     * @const int defined from the BSD Syslog message severities
     * @link http://tools.ietf.org/html/rfc3164
     */
    const EMERG     = 0;
    const ALERT     = 1;
    const CRIT      = 2;
    const ERR       = 3;
    const WARN      = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    /**
     * For application data audit
     */
    const AUDIT     = 16;

    /**
     * The format of the date used for a log entry (ISO 8601 date)
     *
     * @see http://www.php.net/manual/en/function.date.php
     * @var string
     */
    protected $dateTimeFormat = '';

    /**
     * List of priority code => priority (short) name
     *
     * @var array
     */
    protected $priorities = array(
        self::EMERG     => 'EMERG',
        self::ALERT     => 'ALERT',
        self::CRIT      => 'CRIT',
        self::ERR       => 'ERR',
        self::WARN      => 'WARN',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG',

        // Application audit
        self::AUDIT     => 'AUDIT',
    );

    /**
     * Writers
     *
     * @var SplPriorityQueue
     */
    protected $writers;

    /**
     * Constructor
     *
     * @return Logger
     */
    public function __construct($options = array())
    {
        $this->writers = new SplPriorityQueue();
        if (!empty($options['writer'])) {
            foreach ($options['writer'] as $writer => $opt) {
                $this->addWriter($writer, $opt);
            }
        }
    }

    public function priorityName($priorityValue)
    {
        return isset($this->priorities[$priorityValue]) ? $this->priorities[$priorityValue] : null;
    }

    /**
     * Shutdown all writers
     *
     * @return void
     */
    public function shutdown()
    {
        foreach ($this->writers as $writer) {
            try {
                $writer->shutdown();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Return the format of DateTime
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }

    /**
     * Set the format of DateTime
     *
     * @see    http://www.php.net/manual/en/function.date.php
     * @param  string $format
     * @return Logger
     */
    public function setDateTimeFormat($format)
    {
        $this->dateTimeFormat = (string) $format;
        return $this;
    }

    /**
     * Get writer instance
     *
     * @param string $name
     * @param array|null $options
     * @return Writer
     */
    public function writerPlugin($name, array $options = null)
    {
        $class = __NAMESPACE__ . '\\Writer\\' . ucfirst($name);
        if (!class_exists($class)) {
            $class = 'Zend\\Log\\Writer\\' . ucfirst($name);
        }
        return new $class($options);
    }

    /**
     * Add a writer to a logger
     *
     * @param  string|Writer $writer
     * @param  array $options
     * @return Logger
     */
    public function addWriter($writer, $priority = 1, array $options = array())
    {
        if (is_string($writer)) {
            $writer = $this->writerPlugin($writer, $options);
        } elseif (!$writer instanceof Writer\WriterInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Writer must implement Zend\Log\Writer; received "%s"',
                is_object($writer) ? get_class($writer) : gettype($writer)
            ));
        }
        $priority = is_int($options) ? $options : (isset($options['priority']) ? $options['priority'] : 1);
        $this->writers->insert($writer, $priority);

        return $this;
    }

    /**
     * Get writers
     *
     * @return SplPriorityQueue
     */
    public function getWriters()
    {
        return $this->writers;
    }
    
    /**
     * Set the writers
     *
     * @param  SplPriorityQueue $writers
     * @throws Exception\InvalidArgumentException
     * @return Logger
     */
    public function setWriters($writers)
    {
        if (!$writers instanceof SplPriorityQueue) {
            throw new \InvalidArgumentException('Writers must be a SplPriorityQueue of Zend\Log\Writer');
        }
        foreach ($writers->toArray() as $writer) {
            if (!$writer instanceof Writer\WriterInterface) {
                throw new \InvalidArgumentException('Writers must be a SplPriorityQueue of Zend\Log\Writer');
            }
        }
        $this->writers = $writers;
        return $this;
    }

    /**
     * Add a message as a log entry
     *
     * @param  int $priority
     * @param  mixed $message
     * @param  array|Traversable|int $extra
     * @return Logger
     * @throws \InvalidArgumentException if message can't be cast to string
     * @throws \InvalidArgumentException if extra can't be iterated over
     */
    public function log($priority, $message, $extra = array())
    {
        if (!is_int($priority) || !isset($this->priorities[$priority])) {
            throw new \InvalidArgumentException('Invalid priority');
        }
        if (is_object($message) && !method_exists($message, '__toString')) {
            throw new \InvalidArgumentException(
                '$message must implement magic __toString() method'
            );
        }

        if (is_int($extra)) {
            $time = $extra;
            $extra = array();
        } elseif (isset($extra['time'])) {
            $time = $extra['time'];
            unset($extra['time']);
        } else {
            $time = microtime(true);
        }

        if (!is_array($extra) && !$extra instanceof Traversable) {
            throw new \InvalidArgumentException(
                '$extra must be an array or implement Traversable'
            );
        } elseif ($extra instanceof Traversable) {
            $extra = ArrayUtils::iteratorToArray($extra);
        }

        if ($this->writers->count() === 0) {
            throw new \RuntimeException('No log writer specified');
        }

        if ($this->dateTimeFormat) {
            $time = time($time, $this->dateTimeFormat);
        }

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        foreach ($this->writers->toArray() as $writer) {
            $writer->write(array(
                'timestamp'    => $time,
                'priority'     => (int) $priority,
                'priorityName' => $this->priorities[$priority],
                'message'      => (string) $message,
                'extra'        => $extra
            ));
        }

        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function emerg($message, $extra = array())
    {
        return $this->log(static::EMERG, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function alert($message, $extra = array())
    {
        return $this->log(static::ALERT, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function crit($message, $extra = array())
    {
        return $this->log(static::CRIT, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function err($message, $extra = array())
    {
        return $this->log(static::ERR, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function warn($message, $extra = array())
    {
        return $this->log(static::WARN, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function notice($message, $extra = array())
    {
        return $this->log(static::NOTICE, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function info($message, $extra = array())
    {
        return $this->log(static::INFO, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function debug($message, $extra = array())
    {
        return $this->log(static::DEBUG, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function audit($message, $extra = array())
    {
        return $this->log(static::AUDIT, $message, $extra);
    }
}
