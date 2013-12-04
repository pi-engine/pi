<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Pi\Application\Bootstrap\Resource\AdminMode;
use Zend\View\Helper\AbstractHelper;
use Module\System\Menu;

/**
 * Back-office navigation helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AdminNav extends AbstractHelper
{
    /** @var string Module name */
    protected $module;

    /** @var string Side menu content */
    protected $side;

    /** @var string Top menu content */
    protected $top;

    /**
     * Invoke for helper
     *
     * @param string $module
     * @return self
     */
    public function __invoke($module = '')
    {
        $this->module = $module ?: Pi::service('module')->current();

        return $this;
    }

    /**
     * Get back-office mode list
     *
     * @return array
     */
    public function modes()
    {
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];
        $modes = Menu::modes($mode);

        return $modes;
    }

    /**
     * Get back-office side menu
     *
     * @return array
     */
    public function main()
    {
        $module = $this->module ?: Pi::service('module')->currrent();
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $navigation = '';
        // Get manage mode navigation
        if (AdminMode::MODE_ADMIN == $mode && 'system' == $module) {
            $routeMatch = Pi::engine()->application()->getRouteMatch();
            $params = $routeMatch->getParams();
            if (empty($params['name'])) {
                $params['name'] = 'system';
            }
            $navigation = Menu::mainComponent(
                $params['name'],
                $params['controller']
            );
        // Get operation mode navigation
        } elseif (AdminMode::MODE_ACCESS == $mode) {
            $navigation = Menu::mainOperation($module);
        }

        return $navigation;
    }

    /**
     * Get back-office sub menu
     *
     * @param string $class
     *
     * @return string
     */
    public function sub($class = '')
    {
        $module = $this->module ?: Pi::service('module')->currrent();
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $navigation = '';
        // Managed components
        if (AdminMode::MODE_ADMIN == $mode && 'system' == $module) {
            //$currentModule = $_SESSION['PI_BACKOFFICE']['module'];
            //$navigation = Menu::subComponent($currentModule);
        // Module operations
        } elseif (AdminMode::MODE_ACCESS == $mode) {
            $navigation = Menu::subOperation($module, $class);
        }

        return $navigation;
    }

    /**
     * Get back-office top menu
     *
     * @param string $class
     *
     * @return string
     */
    public function top($class = '')
    {
        $module = $this->module ?: Pi::service('module')->currrent();
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $navigation = '';
        // Managed components
        if (AdminMode::MODE_ADMIN == $mode && 'system' == $module) {
            $currentModule = $_SESSION['PI_BACKOFFICE']['module'];
            $navigation = Menu::subComponent($currentModule, $class);
            // Module operations
        } elseif (AdminMode::MODE_ACCESS == $mode) {
            //$navigation = Menu::subOperation($module);
        }

        return $navigation;
    }
}
