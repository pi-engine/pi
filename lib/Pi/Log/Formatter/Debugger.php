<?php
/**
 * Pi Debugger Formatter
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
use Zend\Log\Formatter\FormatterInterface;

class Debugger implements FormatterInterface
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
                        '<div class="message %priorityName%" style="clear: both;">[%priorityName%] %location%</div>' . PHP_EOL .
                        '<div class="message">%message%</div>' . PHP_EOL .
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
        if (!empty($event['timestamp'])) {
            $event['timestamp'] = date($this->getDateTimeFormat(), intval($event['timestamp'])) . substr($event['timestamp'], strpos($event['timestamp'], '.'), 5);
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
                /**#@++
                * Remove path prefix for security considerations
                */
                //$location .= sprintf(' in %s', $this->sanitizePaths($event['extra']['file']));
                $location .= sprintf(' in %s', Security::sanitizePath($event['extra']['file']));
                /**#@-*/
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
     * Remove path prefix for security considerations
     *
     * @staticvar array $paths
     * @param string $str
     * @return string
     */
    protected function sanitizePaths($str)
    {
        static $paths;

        if (!isset($paths)) {
            // Loads all path settings from host data
            $paths = Pi::host()->get('path');
            $lens = array();
            foreach ($paths as $root => $v) {
                $lens[] = strlen($v);
            }
            // Sort the paths by their lengths in reverse
            array_multisort($lens, SORT_NUMERIC, SORT_DESC, $paths);
        }
        if (DIRECTORY_SEPARATOR != '/') {
            $str = str_replace(DIRECTORY_SEPARATOR, '/', $str);
        }
        foreach ($paths as $root => $v) {
            $str  = str_replace($v . '/', $root . '/', $str);
        }
        return $str;
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
