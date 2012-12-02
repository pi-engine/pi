<?php
/**
 * Form element Carousel template select class
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
 * @package         Module\Widget
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\Widget\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class CarouselTemplate extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $list = array();
            $templatePath = sprintf('%s/template/block', Pi::service('module')->path('widget'));
            $iterator = new \DirectoryIterator($templatePath);
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                    continue;
                }
                $fileName = $fileinfo->getFilename();
                if (!preg_match('/^__carousel\-[a-z0-9_\-]+\.phtml$/', $fileName)) {
                    continue;
                }
                $templateName = substr($fileName, 11, -6);
                $template = substr($fileName, 0, -6);
                $list[$template] = $templateName;
            }
            $this->valueOptions = $list;
        }

        return $this->valueOptions;
    }
}
