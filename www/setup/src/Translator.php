<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Setup;

/**
 * Translator handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Translator
{
    /** @var string */
    protected static $basePath = '';

    /** @var array */
    protected static $data;

    /** @var string */
    protected static $locale = 'en';

    /** @var array */
    protected static $options = array(
        'length'    => 0,
        'delimiter' => ';',
        'enclosure' => '"'
    );

    /**
     * Translate a message
     *
     * @param string $messageId
     * @return string
     */
    public static function translate($messageId)
    {
        $message = isset(static::$data[static::$locale][$messageId])
            ? static::$data[static::$locale][$messageId] : $messageId;

        return $message;
    }

    /**
     * Set base path
     *
     * @param string $path
     * @return void
     */
    public static function setPath($path)
    {
        static::$basePath = $path;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return void
     */
    public static function setLocale($locale)
    {
        static::$locale = $locale;
    }

    /**
     * Load translation file of a domain
     *
     * @param string $domain
     * @return void
     */
    public static function loadDomain($domain)
    {
        $filename = sprintf(
            '%s/%s/%s.csv',
            static::$basePath,
            static::$locale,
            $domain
        );
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
     * @param  string  $filename  Full path to CSV file
     * @param  array   $options   OPTIONAL Options to use
     * @return array
     * @throws \InvalidArgumentException for file failure
     */
    protected static function loadFile($filename, array $options = array())
    {
        $result = array();
        $options = $options + static::$options;
        $file = @fopen($filename, 'rb');
        if (!$file) {
            throw new \InvalidArgumentException(
                'Error opening translation file \'' . $filename . '\'.'
            );
        }

        while (($data = fgetcsv($file, $options['length'],
                $options['delimiter'], $options['enclosure'])
            ) !== false
        ) {
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
