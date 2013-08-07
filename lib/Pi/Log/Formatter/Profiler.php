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
 * Profiler formatter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profiler implements FormatterInterface
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
                    . '<div class="message info">'
                    . '%name% - time: %timer%; realmem: %realmem%;'
                    . ' emalloc: %emalloc%</div>' . PHP_EOL
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
        $event['timestamp'] = date(
                $this->getDateTimeFormat(),
                intval($event['timestamp'])
        ) . substr($event['timestamp'], strpos($event['timestamp'], '.'), 5);
        $event['timer'] = sprintf('%.4f', $event['timer']);
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
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;

        return $this;
    }
}
