<?php
/**
 * Pi Engine File rename filter
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
 * @since           1.0
 * @package         Pi\Filter
 * @version         $Id$
 */

namespace Pi\Filter\File;

use Closure;
use Pi;
use Zend\Filter\File\Rename as ZendRename;

class Rename extends ZendRename
{
    protected $source;

    /**
     * Class constructor
     *
     * Options argument may be either a string, a Zend_Config object, or an array.
     * If an array or Zend_Config object, it accepts the following keys:
     * 'source'    => Source filename or directory which will be renamed
     * 'target'    => Target filename or directory, the new name of the source file
     * 'overwrite' => Shall existing files be overwritten ?
     * 'randomize' => Shall target files have a random postfix attached?
     *
     * @param  string|Closure|array|Traversable $options Target file or directory to be renamed
     */
    public function __construct($options)
    {
        if ($options instanceof Closure) {
            $options = array('target' => $options);
        }

        parent::__construct($options);
    }

    /**
     * Internal method for creating the file array
     * Supports single and nested arrays
     *
     * @param  array $options
     * @return array
     */
    protected function _convertOptions($options)
    {
        $files = array();
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $this->_convertOptions($value);
                continue;
            }

            switch ($key) {
                case "source":
                    $files['source'] = (string) $value;
                    break;

                case 'target' :
                    /**#@+
                     * Enable Closure callback by Taiwen Jiang
                     */
                    //$files['target'] = (string) $value;
                    $files['target'] = $value;
                    /**#@-*/
                    break;

                case 'overwrite' :
                    $files['overwrite'] = (bool) $value;
                    break;

                case 'randomize' :
                    $files['randomize'] = (boolean) $value;
                    break;

                default:
                    break;
            }
        }

        if (empty($files)) {
            return $this;
        }

        if (empty($files['source'])) {
            $files['source'] = '*';
        }

        if (empty($files['target'])) {
            $files['target'] = '*';
        }

        if (empty($files['overwrite'])) {
            $files['overwrite'] = false;
        }

        if (empty($files['randomize'])) {
            $files['randomize'] = false;
        }

        $found = false;
        foreach ($this->files as $key => $value) {
            if ($value['source'] == $files['source']) {
                $this->files[$key] = $files;
                $found             = true;
            }
        }

        if (!$found) {
            $count               = count($this->files);
            $this->files[$count] = $files;
        }

        return $this;
    }

    /**
     * Defined by Zend\Filter\Filter
     *
     * Renames the file $value to the new name set before
     * Returns the file $value, removing all but digit characters
     *
     * @param  string|array $value Full path of file to change or $_FILES data array
     * @return string|array The new filename which has been set
     */
    public function filter($value)
    {
        if (is_array($value)) {
            $this->setSource($value);
        }
        return parent::filter($value);
    }

    /**
     * Internal method to resolve the requested source
     * and return all other related parameters
     *
     * @param  string $file Filename to get the informations for
     * @return array|string
     */
    protected function _getFileName($file)
    {
        $rename = array();
        foreach ($this->files as $value) {
            if ($value['source'] == '*') {
                if (!isset($rename['source'])) {
                    $rename           = $value;
                    $rename['source'] = $file;
                }
            }

            if ($value['source'] == $file) {
                $rename = $value;
                break;
            }
        }

        if (!isset($rename['source'])) {
            return $file;
        }

        if (!isset($rename['target']) || ($rename['target'] == '*')) {
            $rename['target'] = $rename['source'];
        }

        /**#@+
         * Added by Taiwen Jiang
         */
        $this->parseStrategy($rename);
        /**#@-*/

        if (is_dir($rename['target'])) {
            $name = basename($rename['source']);
            $last = $rename['target'][strlen($rename['target']) - 1];
            if (($last != '/') && ($last != '\\')) {
                $rename['target'] .= DIRECTORY_SEPARATOR;
            }

            $rename['target'] .= $name;
        }

        return $rename;
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * Generate target on-fly
     *
     * @param array $file
     */
    protected function parseStrategy(&$file)
    {
        if (is_array($this->source) && $this->source['tmp_name'] == $file['source']) {
            $name = $this->source['name'];
        } else {
            $name = $file['source'];
        }
        if ($file['target'] instanceof Closure) {
            $target = $file['target']($name);
        } elseif (false !== strpos($file['target'], '%')) {
            $pos = strrpos($name, '.');
            if (false !== $pos) {
                $extension = substr($name, $pos);
                $name = substr($name, 0, $pos);
            } else {
                $extension = '';
            }

            $terms = array(
                '%term%'        => $name,
                '%random%'      => uniqid(),
                '%date:l%'      => date('YmdHis'),
                '%date:m%'      => date('Ymd'),
                '%date:s%'      => date('Ym'),
                '%time%'        => time(),
                '%microtime%'   => microtime(),
            );
            $target = str_replace(array_keys($terms), array_values($terms), $file['target']) . $extension;
        } else {
            return;
        }
        $file['target'] = $target;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }
    /**#@-*/
}
