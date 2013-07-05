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

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

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
        $this->redirect()->toRoute('', array('controller' => 'dashboard', 'action' => 'system'));
        return;

        $mode = $this->params('mode');
        if ($mode) {
            $this->redirect()->toRoute('', array('controller' => 'dashboard', 'mode' => $mode));
        } else {
            $this->redirect()->toRoute('', array('controller' => 'dashboard'));
        }
        //$this->view()->setTemplate(false);
        return;
    }
}
