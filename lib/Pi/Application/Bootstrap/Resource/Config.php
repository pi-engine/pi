<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        // Load system general configuration
        Pi::config()->loadDomain();

        // Setup timezone
        $timezone = Pi::config('timezone');
        if ($timezone) {
            date_default_timezone_set($timezone);
        }
    }
}
