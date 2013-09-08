<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Db\Sql\Where;

/**
 * Comment service
 *
 * - addPost(array $data)
 * - getPost($id)
 * - getRoot(array $condition|$id)
 * - getTarget($root)
 * - getList(array $condition|$root, $limit, $offset, $order)
 * - getCount(array $condition|$root)
 * - getUrl($root, $id)
 * - updatePost($id, array $data)
 * - deletePost($id)
 * - approve($id, $flag)
 * - enable($root, $flag)
 * - delete($root, $flag)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Comment extends AbstractService
{
    /**
     * Is comment service available
     *
     * @return bool
     */
    public function active()
    {
        return Pi::service('module')->isActive('comment');
    }

    /**
     * Add comment of an item
     *
     * @param array $data   Data of uid, content, module, item, category, time
     *
     * @return int|bool
     */
    public function addPost(array $data)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->add($data);
    }

    /**
     * Get a comment
     *
     * @param int $id
     *
     * @return array|bool   uid, content, time, active, IP
     */
    public function getPost($id)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->get($id);
    }

    /**
     * Get root
     *
     * @param int|array $condition
     *
     * @return array|bool    module, category, item, callback, active
     */
    public function getRoot($condition)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getRoot($condition);
    }

    /**
     * Get target content
     *
     * @param int $root
     *
     * @return array|bool    Title, url, uid, time
     */
    public function getTarget($root)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getTarget($root);
    }

    /**
     * Get multiple comments
     *
     * @param int|array|Where $condition Root id or conditions
     * @param int       $limit
     * @param int       $offset
     * @param string    $order
     *
     * @return array|bool
     */
    public function getList($condition, $limit, $offset = 0, $order = '')
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getList($condition, $limit, $offset, $order);
    }

    /**
     * Get comment count
     *
     * @param int|array|Where     $condition Root id or conditions
     *
     * @return int|bool
     */
    public function getCount($condition)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getCount($condition);
    }

    /**
     * Get URL to a comment or a comment root
     *
     * @param int $root
     * @param int $id
     *
     * @return string|bool
     */
    public function getUrl($root, $id = null)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getUrl($root, $id);
    }

    /**
     * Update a comment
     *
     * @param int   $id
     * @param array $data
     *
     * @return bool
     */
    public function updatePost($id, array $data)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->update($id, $data);
    }

    /**
     * Delete a comment
     *
     * @param int   $id
     *
     * @return bool
     */
    public function deletePost($id)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->delete($id);
    }

    /**
     * Approve/Disapprove a comment
     *
     * @param int  $id
     * @param bool $flag
     *
     * @return bool
     */
    public function approve($id, $flag = true)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->approve($id, $flag);
    }

    /**
     * Enable/Disable comments for a target
     *
     * @param array|int $root
     * @param bool      $flag
     *
     * @return bool
     */
    public function enable($root, $flag = true)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->enable($root, $flag);
    }

    /**
     * Delete comment root and its comments
     *
     * @param int  $root
     *
     * @return bool
     */
    public function delete($root)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->deleteRoot($root);
    }
}
