<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Module;

use Pi;
use Pi\Mvc\Controller\ActionController;
//use Pi\Application\Bootstrap\Resource\AdminMode;
use Module\System\Menu;

/**
 * Module admin menu controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class MenuController extends ActionController
{
    public function permissionException()
    {
        return true;
    }

    /**
     * Load side main menu for operations
     *
     * @return array
     */
    public function indexAction()
    {
        $module = $this->params('module');
        $navigation = Menu::mainOperation($module);

        return $navigation;
    }

    /**
     * Get side main menu for management
     *
     * @return array
     */
    public function sideAction()
    {
        $module = $this->params('name', 'system');
        $controller = $this->params('controller');
        $navigation = Menu::mainComponent($module, $controller);

        return $navigation;
    }

    /**
     * Load module component top menu
     */
    public function componentAction()
    {
        $module = $this->params('name');
        $navigation = Menu::subComponent($module);

        return $navigation;
    }

    /**
     * Load module admin sub menu
     */
    public function subAction()
    {
        $module = $this->params('name');
        $class  = $this->params('class', 'dropdown-menu');
        $navigation = Menu::subOperation($module, array('ulClass' => $class));

        return $navigation;
    }
}