<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Comment post controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PostController extends ActionController
{
    /**
     * Comment post
     *
     * @return string
     */
    public function indexAction()
    {
        $id = _get('id');
    }

    public function submitAction()
    {
    }

    public function approveAction()
    {
        $flag = _get('approve');
    }

    public function deleteAction()
    {
        $id = _get('id');
    }
}
