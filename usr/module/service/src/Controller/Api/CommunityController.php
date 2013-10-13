<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Service\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Community webservice controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CommunityController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return string
     */
    public function indexAction()
    {
        $params = _get();
        if ($params) {
            $result = array(
                'status'    => 1,
                'message'   => 'GET data received.',
                'data'      => $params,
            );
        } else {
            $result = array(
                'status'    => 0,
                'message'   => 'GET data failed.',
            );

        }

        return $result;
    }

    public function postAction()
    {
        $params = _post();
        if ($params) {
            $result = array(
                'status'    => 1,
                'message'   => 'POST data received.',
                'data'      => $params,
            );
        } else {
            $result = array(
                'status'    => 0,
                'message'   => 'GET data failed.',
            );

        }

        return $result;
    }

    public function testAction()
    {
        $uri = 'http://slave/api/service/community';
        $params = array(
            'test'  => 'Test',
            'int'   => 5,
            'array' => array('a', 'b', 'c'),
        );
        $result = Pi::service('remote')->get($uri, $params);
        vd($result);
        $uri = 'http://slave/api/service/community/post';
        $result = Pi::service('remote')->post($uri, $params);
        vd($result);

        $this->view()->setTemplate(false);
    }
}
