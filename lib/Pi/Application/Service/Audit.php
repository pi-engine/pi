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

/**
 * Auditing service
 *
 * The service provides a variety of ways logging run-time structured data
 * into files.
 * An audit task could be specified in `var/config/service.audit.php`
 * then called in applications,
 * it could also be called directly in run-time in applications on fly.
 *
 * Definition of configuration:
 *
 * - Full specified mode with option array:
 *   - file: path to the log file
 *   - timeformat: time stamp format in log
 *   - format: logged data format, for example "%time% %d %s [%s]"
 *
 * ```
 *  'full-mode-audit'   => array(
 *      'file'          => <path/to/full.log>,
 *      'timeformat'    => <date-format>,
 *      'format'        => '%time% %d %s [%s]',
 *  )
 * ```
 *
 * - CSV mode with option array:
 *   - file: path to the log file
 *   - timeformat: time stamp format in log
 *   - format: "csv", data are stored in CSV format
 *
 * ```
 *  'csv-mode-audit'    => array(
 *      'file'          => <path/to/csv.log>,
 *      'format'        => 'csv', // fixed
 *      'timeformat'    => <date-format>,
 *  ),
 * ```
 *
 * - Custom mode with option array (could be empty):
 *   - file: optional; if file is not specified, log data will be stored in
 *      `var/log/<audit-name>.log`
 *   - timeformat: optional, default as `c`
 *   - format: optional, default as `csv`
 *
 * ```
 *  'custom-mode-audit'  => array(
 *      ['file'          => <path/to/audit.log>,]
 *      ['timeformat'    => <date-format>,]
 *      ['format'        => <data-format>,]
 *  )
 * ```
 *
 * - Custom mode with string option:
 *   - file: the specified string is used as log file
 *   - timeformat: "c"
 *   - format: "csv"
 *
 * ```
 *  'audit-name' => <path/to/audit.log>
 * ```
 *
 * Log data with an audit defined in var/config/service.audit.php:
 *
 * ```
 *  $args = array(rand(), 'var1', 'var, val and exp');
 *  Pi::service('audit')->log('full-mode-audit', $args);
 *  Pi::service('audit')->log('csv-mode-audit', $args);
 *  Pi::service('audit')->log('audit-name', $args);
 * ```
 *
 * Log data directly to a log file on fly (not pre-defined):
 *
 * ```
 *  $args = array(rand(), 'var1', 'var, val and exp');
 *  Pi::service('audit')->log('audit-on-fly', $args);
 * ```
 *
 * Define and attach an audit then write log data:
 *
 * ```
 *  $args = array(rand(), 'var1', 'var, val and exp');
 *  Pi::service('audit')->attach('custom-audit', array(
 *      'file'  => <path/to/custom.csv>
 *  ));
 *  Pi::service('audit')->log('custom-audit', $args);
 * ```
 *
 * @see var/config/service.audit.php for audit service configuration
 * @see http://www.php.net/manual/en/function.date.php for date format
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

    /** @var array Log items */
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
            $options = isset($this->options[$name])
                ? $this->options[$name] : array();
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
     * ```
     *   Pi::service('audit')->log(<operation-name>,
     *      array(<val1>, <val2>, <val3>, ..., <valn>));
     * ```
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
