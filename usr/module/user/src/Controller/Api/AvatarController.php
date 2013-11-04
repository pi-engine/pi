<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ActionController;

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
class AvatarController extends ActionController
{
    /**
     * Placeholder
     *
     * @return array
     */
    public function indexAction()
    {
        return array('status' => 1);
    }

    /**
     * Gets a user with specified fields
     *
     * @return array
     */
    public function getAction()
    {
        $uid        = $this->params('id');
        $size       = $this->params('size');

        $result     = Pi::service('avatar')->get($uid, $size, false);
        $response   = array(
            'status'    => 1,
            'data'      => $result,
        );

        return $response;
    }

    /**
     * Gets multiple users with specified fields
     *
     * @return array
     */
    public function mgetAction()
    {
        $uid        = $this->params('id');
        $size       = $this->params('size');

        $uids       = $this->splitString($uid);
        $result     = Pi::service('avatar')->getList($uids, $size, false);

        return $result;
    }

    /**
     * Split string delimited by comma `,`
     *
     * @param string $string
     *
     * @return array
     */
    protected function splitString($string = '')
    {
        $result = array();
        if (!$string) {
            return $result;
        }

        $result = explode(',', $string);
        array_walk($result, 'trim');
        $result = array_unique(array_filter($result));

        return $result;
    }

    /**
     * Canonize query strings by convert `*` to `%` for LIKE query
     *
     * @param string $query
     *
     * @return array
     */
    protected function canonizeQuery($query = '')
    {
        $result = array();
        if (!$query) {
            return $result;
        }
        if (is_string($query)) {
            $query = $this->splitString($query);
        }
        array_walk($query, function ($qString) use (&$result) {
            list($identifier, $like) = explode(':', $qString);
            $identifier = trim($identifier);
            $like = trim($like);
            if ($identifier && $like) {
                $like = str_replace(
                    array('%', '*', '_'),
                    array('\\%', '%', '\\_'),
                    $like
                );
                $result[$identifier] = $like;
            }
        });

        return $result;
    }
}