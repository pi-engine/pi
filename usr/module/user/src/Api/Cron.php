<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('cron', 'user')->start();
 */

class Cron extends AbstractApi
{
    public function start()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Check cron active for this module
        if ($config['module_cron']) {

            // Set log
            Pi::service('audit')->log('cron', 'user - Start cron on server');

            $this->cleanOldSession();

            // Set log
            Pi::service('audit')->log('cron', 'user - End cron on server');

        } else {
            // Set log
            Pi::service('audit')->log('cron', 'user - cron system not active for this module');
        }
    }

    /**
     * Clean old sessions from database
     */
    public function cleanOldSession()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        $timeout = (int)$config['cron_clean_session_days_after']; // in days
        $timeout = $timeout * 24 * 60 * 60; // seconds

        $sessionModel = Pi::model('session');
        $delete       = $sessionModel->delete('(modified + lifetime + ' . $timeout . ') < UNIX_TIMESTAMP()');
    }
}