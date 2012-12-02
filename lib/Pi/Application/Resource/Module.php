<?php
/**
 * Bootstrap resource
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
 * @package         Pi\Application
 * @subpackage      Resource
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Resource;

use Pi;
use Zend\Mvc\MvcEvent;

class Module extends AbstractResource
{
    /**
     * @return void
     */
    public function boot()
    {
        // Setup module service and load module config right after access permission check
        $this->application->getEventManager()->attach('dispatch', array($this, 'setup'), 999);
    }

    /**
     * Set current module to module service and load module config after module is dispatched
     *
     * @param MvcEvent $e
     */
    public function setup(MvcEvent $e)
    {
        $module = $e->getRouteMatch()->getParam('module');
        // Load module config
        Pi::service('module')->setModule($module)->config();

        // Load module theme
        if ('front' == $this->application->getSection()) {
            $themes = Pi::config('theme_module', '');
            if (!empty($themes[$module])) {
                Pi::service('theme')->setTheme($themes[$module]);
            }
        }
    }
}
