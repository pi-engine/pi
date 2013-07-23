<?php
/**
 * Audit service configuration
 *
 * Different mode of audits.
 *
 * 
 * - Full specified mode with option array:
 *   - file: path to the log file
 *   - timeformat: time stamp format in log, {@link http://www.php.net/manual/en/function.date.php}
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
 *   - timeformat: time stamp format in log, {@link http://www.php.net/manual/en/function.date.php}
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
 *   - file: optional; if file is not specified, log data will be stored in "var/log/<audit-name>.log"
 *   - timeformat: optional, default as "c"
 *   - format: optional, default as "csv"
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
 */

$path = Pi::path('log');
return array(
    'full'  => array(
        'file'          => $path . '/full.log',
        'timeformat'    => 'c',
        'format'        => '%time% %d %s [%s]',
    ),
    'csv'   => array(
        'file'          => $path . '/csv.log',
        'format'        => 'csv',
        'timeformat'    => 'c',
    ),
    'relative'  => array(
        'timeformat'    => 'c',
    ),
    'lean'      => array(
        'file'          => $path . '/lean.log',
    ),
    'easy'      => $path . '/easy.log',
    'simple'    => array(),
);
