<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\I18n\Translator\Loader;

use Pi;
use Zend\I18n\Translator\Loader\FileLoaderInterface;
use Zend\I18n\Translator\TextDomain;
use Zend\I18n\Exception;
use Zend\Stdlib\ErrorHandler;

/**
 * CSV file content loader
 *
 * @see http://www.php.net/manual/en/function.fgetcsv.php
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Csv implements FileLoaderInterface
{
    /**
     * File extension
     * @var string
     */
    protected $fileExtension = '.csv';

    /**
     * Options for CSV file
     * @var array
     */
    protected $options = array(
        'delimiter' => ',',
        'length'    => 0,
        'enclosure' => '"',
    );

    /**
     * Set options
     *
     * @param  array $options
     * @return self
     */
    public function setOptions($options = array())
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
        $filename .= $this->fileExtension;
        $messages = array();

        ErrorHandler::start();
        $file = fopen($filename, 'rb');
        $error = ErrorHandler::stop();
        if (false === $file) {
            return false;
        }

        while(($data = fgetcsv(
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
                $singular = array_shift($data);
                $messages[$singular] = $data;
            }
        }

        $textDomain = new TextDomain($messages);

        return $textDomain;
    }
}
