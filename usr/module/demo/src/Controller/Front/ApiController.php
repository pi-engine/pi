<?php
/**
 * Demo API controller
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
 * @version         $Id$
 */

namespace Module\Demo\Controller\Front;
use Pi\Mvc\Controller\ActionController;

class ApiController extends ActionController
{
    public function indexAction()
    {
        $this->view()->setTemplate(false);
        return sprintf('Called from %s', __METHOD__);
    }

    public function testAction()
    {
        $this->view()->assign('title', __('Test for Demo API'));
        $this->view()->assign('params', $this->params()->fromRoute());
    }
}
