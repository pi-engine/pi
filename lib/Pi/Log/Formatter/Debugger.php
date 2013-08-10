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
use Zend\Log\Formatter\FormatterInterface;

/**
 * Debugger formatter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Debugger implements FormatterInterface
{
    /** @var string Format specifier for log messages */
    protected $format;

    /** @var string DateTime format */
    protected $dateTimeFormat = 'H:i:s';

    /**
     * Class constructor
     *
     * @param  null|string  $format  Format specifier for log messages
     */
    public function __construct($format = null)
    {
        if ($format === null) {
            $format = '<div class="pi-event">' . PHP_EOL
                    . '<div class="time">%timestamp%</div>' . PHP_EOL
                    . '<div class="message %priorityName%"'
                    . ' style="clear: both;">'
                    . '[%priorityName%] %location%</div>' . PHP_EOL
                    . '<div class="message">%message%</div>' . PHP_EOL
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
        if (!empty($event['timestamp'])) {
            $event['timestamp'] = date(
                    $this->getDateTimeFormat(),
                    intval($event['timestamp'])
            ) . substr($event['timestamp'],
                       strpos($event['timestamp'], '.'), 5);
        }
        if (!empty($event['priorityName'])) {
            $event['priorityName'] = strtolower($event['priorityName']);
        }
        if (isset($event['extra'])) {
            $location = '';
            if (!empty($event['extra']['line'])) {
                $location .= sprintf('#%d', $event['extra']['line']);
            }
            if (!empty($event['extra']['file'])) {
                // Remove path prefix for security concerns
                $location .= sprintf(
                    ' in %s',
                    Pi::service('security')->path($event['extra']['file'])
                );
            }
            $event['location'] = $location;
            unset($event['extra']);
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
     * @return void
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;

        return $this;
    }
}
