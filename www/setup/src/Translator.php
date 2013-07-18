<?php
/**
 * Pi Engine Setup Translator
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Setup
 */

namespace Pi\Setup;

class Translator
{
    protected static $basePath = '';
    protected static $data;
    protected static $locale = 'en';
    protected static $options = array(
        'length'    => 0,
        'delimiter' => ';',
        'enclosure' => '"'
    );

    public static function translate($messageId)
    {
        $message = isset(static::$data[static::$locale][$messageId]) ? static::$data[static::$locale][$messageId] : $messageId;
        return $message;
    }

    public static function setPath($path)
    {
        static::$basePath = $path;
    }

    public static function setLocale($locale)
    {
        static::$locale = $locale;
    }

    public static function loadDomain($domain)
    {
        $filename = sprintf('%s/%s/%s.csv', static::$basePath, static::$locale, $domain);
        try {
            if (isset(static::$data[$domain])) {
                static::$data[$domain] += (array) static::loadFile($filename);
            } else {
                static::$data[$domain] = (array) static::loadFile($filename);
            }
        } catch (\Exception $e) {
        }
        return;
    }

    /**
     * Load translation data (CSV file reader)
     *
     * @param  string  $filename  CSV file to add, full path must be given for access
     * @param  array   $option    OPTIONAL Options to use
     * @return array
     */
    protected static function loadFile($filename, array $options = array())
    {
        $result = array();
        $options     = $options + static::$options;
        $file = @fopen($filename, 'rb');
        if (!$file) {
            throw new \InvalidArgumentException('Error opening translation file \'' . $filename . '\'.');
        }

        while (($data = fgetcsv($file, $options['length'], $options['delimiter'], $options['enclosure'])) !== false) {
            if (substr($data[0], 0, 1) === '#') {
                continue;
            }

            if (!isset($data[1])) {
                continue;
            }

            if (count($data) == 2) {
                $result[$data[0]] = $data[1];
            } else {
                $singular = array_shift($data);
                $result[$singular] = $data;
            }
        }

        return $result;
    }
}
