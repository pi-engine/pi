<?php
/**
 * Pi Engine translation loader
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
 * @package         Pi\Translator
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\I18n\Translator\Loader;

use Pi;
use Zend\I18n\Translator\Loader\FileLoaderInterface;
use Zend\I18n\Translator\TextDomain;

class Csv implements FileLoaderInterface
{
    protected $fileExtension = '.csv';

    protected $options = array(
        'delimiter' => ';',
        'length'    => 0,
        'enclosure' => '"',
    );

    /**
     * Set options
     *
     * @param  array $options
     * @return Csv
     */
    public function setOptions($options = array())
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * load(): defined by FileLoaderInterface.
     *
     * @see    LoaderInterface::load()
     * @param  string $locale
     * @param  string $filename
     * @return TextDomain|null
     */
    public function load($locale, $filename)
    {
        $filename .= $this->fileExtension;
        $messages = array();

        if (is_file($filename) && is_readable($filename)) {
            $file = @fopen($filename, 'rb');
            while(($data = fgetcsv($file, $this->options['length'], $this->options['delimiter'], $this->options['enclosure'])) !== false) {
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
        }

        return $messages;

        $textDomain = new TextDomain($messages);
        return $textDomain;
    }
}
