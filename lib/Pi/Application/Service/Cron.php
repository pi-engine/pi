<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Cron service
 *
 * - Pi::service('cron')->start();
 *
 * Save log by audit service on Var/Log/cron.log
 * Each module can add custom log by audit service on Var/Log/cron.log
 *
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */
class Cron extends AbstractService
{
    /**
     * Start cron
     */
    public function start()
    {
        // Set log
        Pi::service('audit')->log('cron', '==================================================');
        Pi::service('audit')->log('cron', 'Start cron system');
        // Set module list
        $moduleList = $this->moduleList();
        // Check all modules
        foreach ($moduleList as $module) {
            if (Pi::service('module')->isActive(strtolower($module))) {
                $class = sprintf('Module\%s\Api\Cron', ucfirst(strtolower($module)));
                if (class_exists($class)) {
                    if (method_exists($class, 'start')) {
                        Pi::api('cron', strtolower($module))->start();
                    }
                }
            }
        }
        // Set log
        Pi::service('audit')->log('cron', 'End cron system');
        Pi::service('audit')->log('cron', '==================================================');
    }

    /**
     * Get list of active modules
     *
     * @return array
     */
    public function moduleList()
    {
        $moduleList = [];
        $modules    = Pi::registry('modulelist')->read('active');
        foreach ($modules as $module) {
            $moduleList[] = $module['name'];
        }

        return $moduleList;
    }
}
