<?php
/**
 * Form element Controller select class
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
 * @package         Module\System
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\System\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class Controller extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module');
            $controllerPath = sprintf('%s/src/Controller/Front', Pi::service('module')->path($module));
            $controllerList = array();
            if (is_dir($controllerPath)) {
                $iterator = new \DirectoryIterator($controllerPath);
                foreach ($iterator as $fileinfo) {
                    if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                        continue;
                    }
                    $fileName = $fileinfo->getFilename();
                    if (!preg_match('/^[A-Z][a-z0-9_]+Controller\.php$/', $fileName)) {
                        continue;
                    }
                    $controllerName = strtolower(substr($fileName, 0, -14));
                    $controllerList[$controllerName] = $controllerName;
                }
            } else {
                $controllerList[''] = __('None');
            }
            $this->valueOptions = $controllerList;
        }

        return $this->valueOptions;
    }
}
