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

class Config extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        // Config will be fetched from database if not cached yet
        //$this->bootstrap->bootResource('db');

        // Load system general configuration
        Pi::config()->loadDomain();

        // Setup timezone
        $timezone = Pi::config('timezone');
        if ($timezone) {
            date_default_timezone_set($timezone);
        }
    }
}
