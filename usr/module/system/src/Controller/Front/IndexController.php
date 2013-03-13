<?php
/**
 * Action controller class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Front;
use Pi\Mvc\Controller\ActionController;
use Pi;

/**
 * Public action controller
 */
class IndexController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return ViewModel
     */
    public function indexAction()
    {

        //return $this->jumpTo404('Demo for 404');
        //return $this->jumpToDenied('Demo for denied');
        //return $this->jumpToException('Demo for 503', 503);

        $this->view()->setTemplate(false);
        return '';
    }

    /**
     * Action called if matched action is denied
     *
     * @return ViewModel
     */
    public function notAllowedAction()
    {
        return $this->jumpToDenied('Access to resource is denied.');

        $statusCode = Pi::registry('user')->isGuest() ? 401 : 403;
        $this->response->setStatusCode($statusCode);
        $event = $this->getEvent();
        //$event->setError(404);
        $event->setError('Access to resource is denied.');//->setResponse($this->response);

        /*
        $statusCode = Pi::registry('user')->isGuest() ? 401 : 403;
        $this->response->setStatusCode($statusCode);
        $event      = $this->getEvent();
        $routeMatch = $event->getRouteMatch();
        $routeMatch->setParam('action', 'not-allowed');

        return $this->view(array(
            'content' => 'Access is not allowed.'
        ));
        */
    }

    /**
     * Action called if matched action does not exist
     *
     * @return ViewModel
     */
    public function notFoundAction()
    {
        return $this->jumpTo404('Required resource is not found.');

        $statusCode = 404;
        $this->response->setStatusCode($statusCode);
        $event = $this->getEvent();
        $event->setError(true);
        $this->view()->assign('message', 'Required resource is not found.');
        /*
        $response   = $this->response;
        $response->setStatusCode(404);
        $event      = $this->getEvent();
        $routeMatch = $event->getRouteMatch();
        $routeMatch->setParam('action', 'not-found');

        return $this->view(array(
            'content' => 'Page not found'
        ));
        */
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
        $this->view()->assign($params);

        /*
        $response = $this->response;

        // It is weird the reponse will be failed in IE with successive redirect with 302
        //$response->setStatusCode(302);
        $headerRefresh = sprintf('%d; url=%s', intval($params['time']), $params['url']);
        $response->getHeaders()->addHeaderLine('Refresh', $headerRefresh);
        */
    }
}
