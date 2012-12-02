<?php
/**
 * Pi Engine Setup Request
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
 * @see             Zend\Translate\Adapter\Csv
 * @since           3.0
 * @package         Pi\Setup
 * @version         $Id$
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

        while(($data = fgetcsv($file, $options['length'], $options['delimiter'], $options['enclosure'])) !== false) {
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
