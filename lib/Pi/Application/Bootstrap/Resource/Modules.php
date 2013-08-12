<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Pi\Application\Bootstrap\ModuleBootstrap;

/**
 * Pre-boot modules
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Modules extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $bootstraps = Pi::registry('bootstrap')->read();
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
