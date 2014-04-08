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
 * Public index controller
 */
class IndexController extends ActionController
{
    /**
     * Default action if none provided
     */
    public function indexAction()
    {
        //return $this->jumpTo404('Demo for 404');
        //return $this->jumpToDenied('Demo for denied');
        //return $this->jumpToException('Demo for 503', 503);

        //$this->flashMessenger()->addMessage('Test for flash messenger.');
        //$this->flashMessenger('Test for flash messenger.');
        $this->view()->setTemplate('system-home');
    }

    /**
     * Action called if matched action is denied
     *
     * @return self
     */
    public function notAllowedAction()
    {
        return $this->jumpToDenied('Access to resource is denied.');
    }

    /**
     * Action called if matched action does not exist
     *
     * @return self
     */
    public function notFoundAction()
    {
        return $this->jumpTo404('Required resource is not found.');
    }

    /**
     * Get user data
     */
    public function userAction()
    {
        $params = $this->params()->fromRoute();
        if (Pi::service('user')->hasIdentity()) {
            $uid  = Pi::service('user')->getId();
            $name = Pi::service('user')->getUser()->get('name');
            $avatar = Pi::service('user')->getPersist('avatar-mini');
            if (!$avatar) {
                $avatar = Pi::service('user')->avatar($uid, 'mini');
                Pi::service('user')->setPersist('avatar-mini', $avatar);
            }
            $user = array(
                'uid'       => $uid,
                'name'      => $name,
                'avatar'    => $avatar,
                'profile'   => Pi::service('user')->getUrl('profile', $params),
                'logout'    => Pi::service('authentication')->getUrl('logout', $params),
                'message'   => Pi::service('user')->message()->getUrl(),
            );
        } else {
            $user = array(
                'uid'       => 0,
            );
        }

        return $user;
    }
}
