<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Comment list controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ListController extends ActionController
{
    /**
     * List of comment posts against conditions
     *
     * @return string
     */
    public function indexAction()
    {
    }

    public function rootAction()
    {
        $id = _get('id');
        $page = _get('p', 1);
        $target = Pi::api('comment')->getTarget($id);
        $posts = Pi::api('comment')->getList($id);
        $count = Pi::api('comment')->getCount($id);
    }

    public function userAction()
    {
        $uid = _get('uid') ?: Pi::user()->getIdentity();
    }

    public function moduleAction()
    {
        $module = _get('name');
        $category = _get('category');
    }
}
