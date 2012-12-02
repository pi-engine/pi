<?php
/**
 * Form element locale class
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
 * @since           3.0
 * @package         Pi\Form
 * @subpackage      ELement
 * @version         $Id$
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Select;
//use Locale as SystemLocale;

class Locale extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $iterator = new \DirectoryIterator(Pi::service('i18n')->getPath('', ''));
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }
                $directory = $fileinfo->getFilename();
                $label = $directory;
                //if (class_exists('SystemLocale')) {
                if (class_exists('\\Locale')) {
                    $label = \Locale::getDisplayName($directory, Pi::config('locale')) ?: $label;
                }
                $this->valueOptions[$directory] = $label;
            }
        }

        return $this->valueOptions;
    }
}
