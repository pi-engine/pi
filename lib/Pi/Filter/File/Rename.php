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

use Pi;
use Zend\Filter\File\Rename as ZendRename;

class Rename extends ZendRename
{
    protected $source;

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
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
            }
        }

        if (!isset($rename['source'])) {
            return $file;
        }

        if (!isset($rename['target']) or ($rename['target'] == '*')) {
            $rename['target'] = $rename['source'];
        }

        /**#@+
         * Added by Taiwen Jiang
         */
        if (false !== strpos($rename['target'], '%')) {
            $rename['target'] = $this->parseStrategy($rename);
        }
        /**#@-*/

        if (is_dir($rename['target'])) {
            $name = basename($rename['source']);
            $last = $rename['target'][strlen($rename['target']) - 1];
            if (($last != '/') and ($last != '\\')) {
                $rename['target'] .= DIRECTORY_SEPARATOR;
            }

            $rename['target'] .= $name;
        }

        //$this->filtered[$file] = $rename;
        return $rename;
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * Generate target on-fly
     *
     * @param array $file
     * @return string
     */
    protected function parseStrategy($file)
    {
        if (isset($this->source['targeted'])) {
            return $this->source['targeted'];
        }
        if (is_array($this->source) && $this->source['tmp_name'] == $file['source']) {
            $name = $this->source['name'];
        } else {
            $name = $file['source'];
        }
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
        $this->source['targeted'] = $target;

        return $target;
    }
    /**#@-*/
}
