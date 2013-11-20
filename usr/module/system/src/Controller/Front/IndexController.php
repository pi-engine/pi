<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
     * For page transition jump
     */
    public function jumpAction()
    {
        $this->view()->setTemplate('jump')->setLayout('layout-simple');
        //$params = Pi::service('session')->jump->params;
        $params = array();
        if (isset($_SESSION['PI_JUMP'])) {
            $params = $_SESSION['PI_JUMP'];
            unset($_SESSION['PI_JUMP']);
        }
        if (empty($params['time'])) {
            $params['time'] = 3;
        }
        if (empty($params['url'])) {
            $params['url'] = Pi::url('www');
        }
        //vd($params);
        $this->view()->assign($params);
    }

    /**
     * Generate sitemap
     */
    public function sitemapAction()
    {
        // Disable debugger message
        Pi::service('log')->mute();

        $this->view()->setTemplate(false)->setLayout('layout-content');
        $sitemapConfig = Pi::registry('navigation')->read('sitemap')
            ?: Pi::registry('navigation')->read('front');
        $sitemap = $this->view()->navigation($sitemapConfig)->sitemap();
        $content = $sitemap->setFormatOutput(true)->render();

        return $content;
    }

    /**
     * User bar
     *
     */
    public function userbarAction()
    {
        $params = $this->params()->fromRoute();
        if (Pi::service('user')->hasIdentity()) {
            $uid  = Pi::service('user')->getId();
            $name = Pi::service('user')->getUser()->get('name');
            $user = array(
                'uid'       => $uid,
                'name'      => $name,
                'profile'   => Pi::service('user')->getUrl('profile', $params),
                'logout'    => Pi::service('authentication')->getUrl('logout', $params),
                'avatar'    => Pi::service('user')->avatar()->get($uid, 'mini'),
            );
            $message = array(
                'url'       => Pi::service('user')->message()->getUrl(),
            );
        } else {
            $user = array(
                'uid'       => 0,
            );
            $message = array();
        }

        return array(
            'user'      => $user,
            'message'   => $message,
        );
    }
}
