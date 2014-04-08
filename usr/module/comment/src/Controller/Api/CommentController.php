<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ApiController;

/**
 * Comment webservice controller
 *
 * Methods:
 *
 * - delete: <id>
 * - get: <id>
 * - insert: array(<field> => <value>)
 * - list: <limit>, <offset>, <order>, array(<queryKey:queryValue>)
 * - patch: <id>, array(<field> => <value>)
 * - undelete: <id>
 * - update: <id>, array(<field> => <value>)
 *
 * - mdelete: array(<id>)
 * - mget: array(<id>)
 * - mundelete: array(<id>)
 *
 * - count: array(<queryKey:queryValue>)
 * - enable: <id>
 * - disable: <id>
 *
 * - menable: array(<id>)
 * - mdisable: array(<id>)
 *
 *
 * @see https://developers.google.com/admin-sdk/directory/v1/reference/users
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CommentController extends ApiController
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
     * Deletes a post
     *
     * @return array
     */
    public function deleteAction()
    {
        return array('status' => 1);
    }

    /**
     * Gets a post
     *
     * @return array
     */
    public function getAction()
    {
        return array('status' => 1);
    }

    /**
     * Gets multiple posts
     *
     * @return array
     */
    public function mgetAction()
    {
        return array('status' => 1);
    }

    /**
     * Gets a list of posts
     *
     * @return array
     */
    public function listAction()
    {
        return array('status' => 1);
    }

    /**
     * Gets count of posts
     *
     * @return array
     */
    public function countAction()
    {
        return array('status' => 1);
    }
}