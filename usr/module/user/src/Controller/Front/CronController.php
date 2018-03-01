<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT
 */
namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class CronController extends ActionController
{
    public function cleanOldSessionAction()
    {
        Pi::api('cron', 'user')->cleanOldSession();

        $this->response->setStatusCode(200);
        return array(
            'message' => "Clean session Ok",
        );
    }

    public function sendReminderAction()
    {
        Pi::api('user', 'user')->sendReminderEmail();

        $this->response->setStatusCode(200);
        return array(
            'message' => "Send reminder OK",
        );
    }
}