<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ApiController;

/**
 * User avatar webservice controller
 *
 * Methods:
 *
 * - get: <id>, <size>
 * - mget: array(<id>), <size>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AvatarController extends ApiController
{
    /**
     * Placeholder
     *
     * @return array
     */
    public function indexAction()
    {
        return ['status' => 1];
    }

    /**
     * Gets a user with specified fields
     *
     * @return array
     */
    public function getAction()
    {
        $uid  = $this->params('id');
        $size = $this->params('size');
        $html = $this->params('html') ?: false;

        $result   = Pi::service('avatar')->get($uid, $size, $html);
        $response = [
            'status' => 1,
            'data'   => $result,
        ];

        return $response;
    }

    /**
     * Gets multiple users with specified fields
     *
     * @return array
     */
    public function mgetAction()
    {
        $uid  = $this->params('id');
        $size = $this->params('size');
        $html = $this->params('html') ?: false;

        $uids   = $this->splitString($uid);
        $result = Pi::service('avatar')->getList($uids, $size, $html);

        return $result;
    }
}