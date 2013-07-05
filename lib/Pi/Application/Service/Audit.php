<?php
/**
 * Pi Engine auditing service
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
 * @package         Pi\Application
 * @subpackage      Service
 */

namespace Pi\Application\Service;

use Pi;

class Audit extends AbstractService
{
    protected $fileIdentifier = 'audit';
    protected $timeformat = 'c';
    protected $format = 'csv';
    protected $logs = array();
    protected $items = array();

    /**
     * Shutdown function, triggered by Pi::shutdown()
     */
    public function shutdown()
    {
        foreach ($this->items as $name => $messages) {
            $this->write($name, $messages);
        }
        return;
    }

    /**
     * Attach a log
     *
     * @param string $name
     * @param array|string|null $options
     * @return void
     */
    public function attach($name, $options = null)
    {
        if (null === $options) {
            $options = isset($this->options[$name]) ? $this->options[$name] : array();
        }
        $options = $options ?: array();
        if (is_string($options)) {
            $options['file'] = $options;
        }
        if (empty($options['file'])) {
            $options['file'] = Pi::path('log') . '/' . $name . '.log';
        }
        if (!isset($options['timeformat'])) {
            $options['timeformat'] = $this->timeformat;
        }
        if (empty($options['format'])) {
            $options['format'] = $this->format;
        }

        $this->logs[$name] = $options;
    }

    /**
     * Write messages to a log
     *
     * @param string $name
     * @param array $messages
     * @return bool
     */
    public function write($name, $messages)
    {
        if (!isset($this->logs[$name])) {
            $this->attach($name);
        }
        $options = $this->logs[$name];
        $file = fopen($options['file'], 'a');
        if (!$file) {
            return false;
        }

        $msgs = array();
        foreach ($messages as $message) {
            list($time, $args) = $message;
            $args = (array) $args;
            $timeString = date($options['timeformat'], $time);
            if ('csv' == strtolower($options['format'])) {
                array_unshift($args, $timeString);
                fputcsv($file, $args);
            } elseif ($options['format']) {
                $msg = str_replace('%time%', $timeString, $options['format']);
                $msg = vsprintf($msg, $args);
                $msgs[] = $msg;
            }
        }
        if ($msgs) {
            $content = implode(PHP_EOL, $msgs);
            fwrite($file, $content);
        }
        fclose($file);

        return true;
    }


    /**
     * Logs an operation
     *
     * <code>
     *   Pi::service('audit')->log('operation_name', array('val1', 'val2', 'val3'));
     * </code>
     *
     * @param  string  $name  log name
     * @param  array|string  $args  parameters to log
     * @return void
     */
    public function log($name, $args)
    {
        $this->items[$name][] = array(time(),$args);
    }
}
