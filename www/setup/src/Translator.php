<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $message = isset(static::$data[$messageId])
            ? static::$data[$messageId] : $messageId;

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
            '%s/%s/%s.mo',
            static::$basePath,
            static::$locale,
            $domain
        );
        //var_dump($filename);
        try {
            if (isset(static::$data)) {
                static::$data += (array) static::loadFile($filename);
            } else {
                static::$data = (array) static::loadFile($filename);
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
        //$result = array();
        //$options = $options + static::$options;
        $file = @fopen($filename, 'rb');
        if (!$file) {
            throw new \InvalidArgumentException(
                'Error opening translation file \'' . $filename . '\'.'
            );
        }

        /*
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
        */

        // Verify magic number
        $magic = fread($file, 4);

        if ($magic == "\x95\x04\x12\xde") {
            $littleEndian = false;
        } elseif ($magic == "\xde\x12\x04\x95") {
            $littleEndian = true;
        } else {
            fclose($file);
            throw new \InvalidArgumentException(sprintf(
                '%s is not a valid gettext file',
                $filename
            ));
        }

        $readInteger = function () use (
            $file,
            $littleEndian
        ) {
            if ($littleEndian) {
                $result = unpack('Vint', fread($file, 4));
            } else {
                $result = unpack('Nint', fread($file, 4));
            }

            return $result['int'];
        };

        $readIntegerList = function ($num) use (
            $file,
            $littleEndian
        ) {
            if ($littleEndian) {
                return unpack('V' . $num, fread($file, 4 * $num));
            }

            return unpack('N' . $num, fread($file, 4 * $num));
        };

        $textDomain = array();

        // Verify major revision (only 0 and 1 supported)
        $majorRevision = ($readInteger() >> 16);

        if ($majorRevision !== 0 && $majorRevision !== 1) {
            fclose($file);
            throw new \InvalidArgumentException(sprintf(
                '%s has an unknown major revision',
                $filename
            ));
        }

        // Gather main information
        $numStrings                   = $readInteger();
        $originalStringTableOffset    = $readInteger();
        $translationStringTableOffset = $readInteger();

        // Usually there follow size and offset of the hash table, but we have
        // no need for it, so we skip them.
        fseek($file, $originalStringTableOffset);
        $originalStringTable = $readIntegerList(2 * $numStrings);

        fseek($file, $translationStringTableOffset);
        $translationStringTable = $readIntegerList(2 * $numStrings);

        // Read in all translations
        for ($current = 0; $current < $numStrings; $current++) {
            $sizeKey                 = $current * 2 + 1;
            $offsetKey               = $current * 2 + 2;
            $originalStringSize      = $originalStringTable[$sizeKey];
            $originalStringOffset    = $originalStringTable[$offsetKey];
            $translationStringSize   = $translationStringTable[$sizeKey];
            $translationStringOffset = $translationStringTable[$offsetKey];

            $originalString = array('');
            if ($originalStringSize > 0) {
                fseek($file, $originalStringOffset);
                $originalString = explode("\0", fread($file, $originalStringSize));
            }

            if ($translationStringSize > 0) {
                fseek($file, $translationStringOffset);
                $translationString = explode("\0", fread($file, $translationStringSize));

                if (count($originalString) > 1 && count($translationString) > 1) {
                    $textDomain[$originalString[0]] = $translationString;

                    array_shift($originalString);

                    foreach ($originalString as $string) {
                        $textDomain[$string] = '';
                    }
                } else {
                    $textDomain[$originalString[0]] = $translationString[0];
                }
            }
        }

        // Read header entries
        if (array_key_exists('', $textDomain)) {
            /*
            $rawHeaders = explode("\n", trim($textDomain['']));

            foreach ($rawHeaders as $rawHeader) {
                list($header, $content) = explode(':', $rawHeader, 2);

                if (trim(strtolower($header)) === 'plural-forms') {
                    $textDomain->setPluralRule(PluralRule::fromString($content));
                }
            }
            */

            unset($textDomain['']);
        }

        fclose($file);

        $result = $textDomain;

        return $result;
    }
}
