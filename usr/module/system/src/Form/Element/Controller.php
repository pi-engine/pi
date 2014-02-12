<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Form element for controller selection
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Controller extends Select
{
    /**
     * Get value options for select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module');
            $controllerPath = sprintf(
                '%s/src/Controller/Front',
                Pi::service('module')->path($module)
            );
            $controllerList = array();
            if (is_dir($controllerPath)) {
                $iterator = new \DirectoryIterator($controllerPath);
                foreach ($iterator as $fileinfo) {
                    if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                        continue;
                    }
                    $fileName = $fileinfo->getFilename();
                    if (!preg_match(
                        '/^[A-Z][a-z0-9_]+Controller\.php$/',
                        $fileName
                    )) {
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
