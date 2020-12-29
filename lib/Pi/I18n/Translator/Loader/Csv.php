<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\I18n\Translator\Loader;

use Laminas\I18n\Translator\Loader\FileLoaderInterface;
use Laminas\I18n\Translator\TextDomain;
use Laminas\Stdlib\ErrorHandler;

/**
 * CSV file content loader
 *
 * @see    http://www.php.net/manual/en/function.fgetcsv.php
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Csv implements FileLoaderInterface
{
    /**
     * File extension
     *
     * @var string
     */
    //protected $fileExtension = '.csv';

    /**
     * Options for CSV file
     *
     * @var array
     */
    protected $options
        = [
            'delimiter' => ',',
            'length'    => 0,
            'enclosure' => '"',
        ];

    /**
     * Set options
     *
     * @param array $options
     *
     * @return self
     */
    public function setOptions($options = [])
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Load a CSV file content
     *
     * {@inheritdoc}
     * @return TextDomain|false
     */
    public function load($locale, $filename)
    {
        //$filename .= $this->fileExtension;
        $messages = [];

        ErrorHandler::start();
        $file  = fopen($filename, 'rb');
        $error = ErrorHandler::stop();
        if (false === $file) {
            return false;
        }

        while (($data = fgetcsv(
                $file,
                $this->options['length'],
                $this->options['delimiter'],
                $this->options['enclosure']
            )) !== false) {
            if (substr($data[0], 0, 1) === '#') {
                continue;
            }

            if (!isset($data[1])) {
                continue;
            }

            if (count($data) == 2) {
                $messages[$data[0]] = $data[1];
            } else {
                $singular            = array_shift($data);
                $messages[$singular] = $data;
            }
        }

        $textDomain = new TextDomain($messages);

        return $textDomain;
    }
}
