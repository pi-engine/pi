<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment;

use Pi;

/**
 * Comment Event Handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Event
{
    /**
     * Comment post submission
     *
     * @param int $id
     * @param string $module
     */
    public static function postsubmit($id, $module)
    {
        return;
    }

    /**
     * Comment post publish
     *
     * @param int $id
     * @param string $module
     */
    public static function postpublish($id, $module)
    {
        // Clear cache for leading comments
        Pi::service('comment')->clearCache($id);

        // Insert timeline item
        Pi::service('comment')->timeline($id);

        return;
    }

    /**
     * Comment post update
     *
     * @param int $id
     * @param string $module
     */
    public static function postupdate($id, $module)
    {
        // Clear cache for leading comments
        Pi::service('comment')->clearCache($id);

        return;
    }

    /**
     * Comment post enable
     *
     * @param int|int[] $id
     * @param string $module
     */
    public static function postenable($id, $module)
    {
        // Clear cache for leading comments
        Pi::service('comment')->clearCache($id);

        // Insert timeline item
        if (is_array($id)) {
            foreach ($id as $cid) {
                Pi::service('comment')->timeline($cid);
            }
        } else {
            Pi::service('comment')->timeline($id);
        }

        return;
    }

    /**
     * Comment post disable
     *
     * @param int|int[] $id
     * @param string $module
     */
    public static function postdisable($id, $module)
    {
        // Clear cache for leading comments
        Pi::service('comment')->clearCache($id);

        return;
    }

    /**
     * Comment post delete
     *
     * @param int|int[] $id
     * @param string $module
     */
    public static function postdelete($id, $module)
    {
        Pi::service('comment')->clearCache($id);

        return;
    }

}
