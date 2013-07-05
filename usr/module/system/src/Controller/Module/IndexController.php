<?php
/**
 * Action controller class
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
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Module;
use Pi\Mvc\Controller\ActionController;
use Pi;

/**
 * Index action controller
 */
class IndexController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $module = $this->params('module');
        $title = __('Admin area');
        $this->view()->assign('title', $title);
        $this->view()->assign('module', $module);
        $this->view()->setTemplate('index', 'system');
    }
}
