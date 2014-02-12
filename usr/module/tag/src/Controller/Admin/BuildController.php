<?php
/**
 * Tag index controller
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
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\Tag
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\Tag\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;


class BuildController extends ActionController
{
    public function flushAction()
    {
        $this->getModel('tag')->delete(array());
        $this->getModel('link')->delete(array());
        $this->getModel('stats')->delete(array());

        $this->jump(array('controller' => 'index', 'action' => 'index'));
    }

    public function importAction()
    {
        set_time_limit(0);

        $generateTags = function () {
            $count = rand(1, 10);
            $tags = array();
            for ($i = 0; $i < $count; $i++) {
                $tags[] = 'tag-' . rand(1, 50);
            }

            if (rand(0, 1)) {
                $tags = implode(' ', $tags);
            }

            return $tags;
        };

        $module = 'demo';
        $type = '';
        for ($i = 1; $i <= 1000; $i++) {
            $item = $i;
            Pi::service('tag')->add($module, $item, $type, $generateTags());
        }

        $this->jump(array('controller' => 'index', 'action' => 'index'));
    }
}