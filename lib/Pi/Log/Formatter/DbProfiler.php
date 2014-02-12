<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Log\Formatter;

use Pi;
use Pi\Log\Logger;
use Zend\Log\Formatter\FormatterInterface;

/**
 * Database profiler formatter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DbProfiler implements FormatterInterface
{
    /** @var string Format specifier for log messages */
    protected $format;

    /** @var string DateTime format */
    protected $dateTimeFormat = 'H:i:s';

    /**
     * Class constructor
     *
     * @param  null|string  $format  Format specifier for log messages
     * @throws \Exception
     */
    public function __construct($format = null)
    {
        if ($format === null) {
            $format = '<div class="pi-event">' . PHP_EOL
                    . '<div class="time">%timestamp%</div>' . PHP_EOL
                    . '<div class="message %priorityName%"'
                    . ' style="clear: both;">'
                    . '[%timer%] %message%</div>' . PHP_EOL
                    . '<div class="message">query: %sql%</div>' . PHP_EOL
                    . '<div class="message">params: %params%</div>' . PHP_EOL
                    . '</div>' . PHP_EOL;
        }

        $this->format = $format;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param array $event Event data
     * @return string Formatted line to write to the log
     */
    public function format($event)
    {
        $output = $this->format;
        /**#@++
         * Remove DB table prefix for security considerations
         */
        $event['message'] = isset($event['message'])
            ? Pi::service('security')->db($event['message']) : '';
        $event['sql'] = Pi::service('security')->db($event['sql']);
        /**#@-*/

        $event['params'] = '';
        $params = $event['parameters'] ?: array();
        foreach ($params as $key => $val) {
            $event['params'] .= '[' . $key . '] ' . $val . ';';
        }
        $event['timestamp'] = date(
                $this->getDateTimeFormat(),
                intval($event['start'])
        ) . substr($event['start'], strpos($event['start'], '.'), 5);
        $event['timer'] = sprintf('%.4f', $event['elapse']);
        if (!$event['status'] && empty($event['priorityName'])) {
            $event['priorityName'] = Pi::service('log')->logger()
                ->priorityName(Logger::ERR);
        }
        if (!empty($event['priorityName'])) {
            $event['priorityName'] = strtolower($event['priorityName']);
        } else {
            $event['priorityName'] = '';
        }
        foreach ($event as $name => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $output = str_replace('%' . $name . '%', $value, $output);
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }

    /**
     * {@inheritDoc}
     * @return self
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;

        return $this;
    }
}
