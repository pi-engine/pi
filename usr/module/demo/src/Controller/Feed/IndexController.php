<?php
/**
 * Feed controller class
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
 * @package         Module\Demo
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\Demo\Controller\Feed;
use Pi\Mvc\Controller\FeedController;
use Pi;

/**
 * Index action controller
 *
 * @see Module\System\Controller\Feed\IndexController
 */
class IndexController extends FeedController
{
    public function indexAction()
    {
        $feed = array(
            'title' =>  'Feed from ' . __METHOD__,
        );
        return $feed;
    }
}
