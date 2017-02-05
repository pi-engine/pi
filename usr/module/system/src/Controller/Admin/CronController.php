<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
/**
 * Theme controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CronController extends ActionController
{
    public function indexAction()
    {
        // Get info from url
        $module = $this->params('module');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Set cron url
        $cronUrl = Pi::url($this->url('default', array(
            'module'      => 'system',
            'controller'  => 'cron',
            'action'      => 'index',
            'password'    => $config['cron_password'],
        )));
        // Set template
        $this->view()->setTemplate('cron-index');
        $this->view()->assign('cronUrl', $cronUrl);
        $this->view()->assign('cronActive', $config['cron_active']);
        $this->view()->assign('cronPassword', $config['cron_password']);
    }
}