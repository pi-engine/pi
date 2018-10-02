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
        $result = Pi::api('user', 'user')->sendReminderEmail();

        $this->response->setStatusCode(200);
        if($result){
            return array(
                'message' => "Send reminder OK",
            );
        } else {
            return array(
                'message' => "Send reminder disabled",
            );
        }
    }

    public function userWithoutPhotoAction()
    {

        $lauchBy = $this->params('launchby');
        if ($lauchBy != 'cron') {
            return;
        }
        try {
            $list = Pi::api('user', 'user')->getUserWithoutPhoto();
            foreach ($list as $userWithoutPhoto) {
                if ($userWithoutPhoto['email'] == "news@sta2m.com") {
                    Pi::api('notification', 'user')->cronUserWithoutPhoto($userWithoutPhoto);
                }
            }
        } catch (Exception $e) {
            $this->errorAction($e->getMessage());
        }

        $this->response->setStatusCode(200);
        return array(
            'message' => "Ok",
            'nb_mail' => count($list),
        );
    }
}