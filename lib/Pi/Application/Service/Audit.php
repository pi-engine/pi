<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Auditing service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Audit extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'audit';

    /** @var string Time format for audit log */
    protected $timeformat = 'c';

    /** @var string File content format for log */
    protected $format = 'csv';

    /** @var array Log container */
    protected $logs = array();

    /** @var array Log iterms */
    protected $items = array();

    /**
     * Shutdown function, triggered by {@link Pi::shutdown()}
     *
     * @return void
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
     * @param string                $name
     * @param array|string|null     $options
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
     * @param string    $name
     * @param array     $messages
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
     *   Pi::service('audit')->log(<operation-name>, array(<val1>, <val2>, <val3>, ..., <valn>));
     * </code>
     *
     * @param  string       $name  Log name
     * @param  array|string $args  Parameters to log
     * @return void
     */
    public function log($name, $args)
    {
        $this->items[$name][] = array(time(),$args);
    }
}
