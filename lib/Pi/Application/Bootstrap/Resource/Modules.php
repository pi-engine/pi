<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Pi\Application\Bootstrap\ModuleBootstrap;

/**
 * Pre-boot modules
 */
class Modules extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $bootstraps = Pi::service('registry')->bootstrap->read();
        if (empty($bootstraps)) {
            return;
        }
        foreach ($bootstraps as $module => $bootstrapClass) {
            if (!class_exists($bootstrapClass)) {
                continue;
            }

            $moduleBootstrap = new $bootstrapClass($this->application);
            if (!$moduleBootstrap instanceof ModuleBootstrap) {
                continue;
            }
            $moduleBootstrap->bootstrap($module);
        }
    }
}
