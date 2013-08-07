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
use Zend\Mvc\MvcEvent;

/**
 * Module boot handling
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Module extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        // Setup module service and load module config
        // right after access permission check
        $this->application->getEventManager()
             ->attach('dispatch', array($this, 'setup'), 999);
    }

    /**
     * Set current module to module service
     * and load module config after module is dispatched
     *
     * @param MvcEvent $e
     * @return void
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
