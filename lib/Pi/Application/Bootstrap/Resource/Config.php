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

/**
 * Config loading
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        // Config will be fetched from database if not cached yet
        //$this->bootstrap->bootResource('db');

        // Environment from `var/config/engine.php`
        $environment = Pi::config()->get('environment');

        // Load system general configuration
        Pi::config()->loadDomain();

        // Keep original environment
        if ($environment) {
            Pi::config()->set('environment', $environment);
        }

        // Setup timezone
        $timezone = Pi::config('timezone');
        if ($timezone) {
            date_default_timezone_set($timezone);
        }
    }
}
