<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Cron controller
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class CronController extends ActionController
{
    public function indexAction()
    {
        // Set template
        $this->view()->setTemplate(false)->setLayout('layout-content');
        // Get info from url
        $module = $this->params('module');
        $password = $this->params('password');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Get password
        if ($config['cron_active'] && !empty($config['cron_password']) && $password == $config['cron_password']) {
            // Do cron
            Pi::service('notification')->cron();
            // return
            return array(
                'message' => 'Cron work fine !',
                'status'  => 1,
                'time'    => time(),
            );
        } else {
            return array(
                'message' => 'Error : password not true!',
                'status'  => 0,
                'time'    => time(),
            );
        }
    }
}