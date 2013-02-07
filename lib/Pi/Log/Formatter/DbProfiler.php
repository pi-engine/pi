<?php
/**
 * Pi DB Profiler Formatter
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


namespace Pi\Log\Formatter;

use Pi;
use Pi\Security;
use Pi\Log\Logger;
use Zend\Log\Formatter\FormatterInterface;

class DbProfiler implements FormatterInterface
{
    /**
     * @var string
     */
    protected $format;

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
            $format = '<div class="pi-event">' . PHP_EOL .
                        '<div class="time">%timestamp%</div>' . PHP_EOL .
                        '<div class="message %priorityName%" style="clear: both;">[%timer%] %message%</div>' . PHP_EOL .
                        '<div class="message">query: %sql%</div>' . PHP_EOL .
                        '<div class="message">params: %params%</div>' . PHP_EOL .
                        '</div>' . PHP_EOL;
        }

        $this->format = $format;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        $output = $this->format;
        /**#@++
         * Remove DB table prefix for security considerations
         */
        $event['message'] = isset($event['message']) ? Security::sanitizeDb($event['message']) : '';
        $event['sql'] = Security::sanitizeDb($event['sql']);
        /**#@-*/

        $event['params'] = '';
        $params = $event['parameters'] ?: array();
        foreach ($params as $key => $val) {
            $event['params'] .= '[' . $key . '] ' . $val . ';';
        }
        $event['timestamp'] = date($this->getDateTimeFormat(), intval($event['start'])) . substr($event['start'], strpos($event['start'], '.'), 5);
        $event['timer'] = sprintf('%.4f', $event['elapse']);
        if (!$event['status'] && empty($event['priorityName'])) {
            $event['priorityName'] = Pi::service('log')->logger()->priorityName(Logger::ERR);
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
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;
        return $this;
    }
}
