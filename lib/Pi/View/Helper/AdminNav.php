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
    public function __invoke($module = 'system')
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get back-office mode list
     *
     * @param string|null $module
     * @return string
     */
    public function modes($module = null)
    {
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];
        //d($_SESSION['PI_BACKOFFICE']['mode']); d($module); exit();

        $modes = array(
            AdminMode::MODE_ACCESS      => array(
                'label' => __('Operation'),
                //'link'  => '',
            ),
            AdminMode::MODE_ADMIN    => array(
                'label' => __('Setting'),
                //'link'  => '',
            ),
            AdminMode::MODE_DEPLOYMENT => array(
                'label' => __('Deployment'),
                'link'  => '',
            ),
        );
        foreach ($modes as $key => &$config) {
            if ($mode == $key) {
                $config['active'] = 1;
            }
            if (isset($config['link'])) {
                continue;
            }
            $config['link'] = $this->view->url('admin', array(
                'module'        => 'system',
                'controller'    => 'dashboard',
                'action'        => 'mode',
                'mode'          => $key,
            ));
        }

        return $modes;
    }

    /**
     * Get back-office side menu
     *
     * @param string|null $module
     * @return string
     */
    public function side($module = null)
    {
        if (null !== $this->side) {
            return $this->side;
        }

        $module = $module ?: $this->module;
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $modules = Pi::registry('modulelist')->read();
        $modulesAllowed = Pi::service('permission')->moduleList($mode);
        $navConfig = array();

        $navigation = '';
        // Get manage mode navigation
        if (AdminMode::MODE_ADMIN == $mode && 'system' == $module) {
            //$modulesAllowed = Pi::service('permission')->moduleList('admin');
            $routeMatch = Pi::engine()->application()->getRouteMatch();
            $params = $routeMatch->getParams();
            if (empty($params['name'])) {
                $params['name'] = 'system';
            }
            // Build managed navigation for all modules
            foreach ($modules as $name => $item) {
                if (!in_array($name, $modulesAllowed)) {
                    $config = array(
                        'uri'   => '#',
                        'class' => 'disabled',
                    );
                } else {
                    $config = array(
                        'route'         => 'admin',
                        'module'        => $params['module'],
                        'controller'    => $params['controller'],
                        'params'        => array(
                            'name'          => $name,
                        ),
                        'active'        => $name == $params['name'] ? 1 : 0,
                    );
                }
                $navConfig[$name] = array_merge($config, array(
                    'label'         => $item['title'],
                ));
            }

            $navigation = $this->view->navigation($navConfig);
        // Get operation mode navigation
        } elseif (AdminMode::MODE_ACCESS == $mode) {
            //$modulesAllowed = Pi::service('permission')->moduleList('access');
            // Build the navigation
            foreach ($modules as $name => $item) {
                if (!in_array($name, $modulesAllowed)) {
                    $config = array(
                        'uri'   => '#',
                        'class' => 'disabled',
                    );
                } else {
                    $config = array(
                        'route'         => 'admin',
                        'module'        => $name,
                        'controller'    => 'dashboard',
                        'active'        => $name == $module ? 1 : 0,
                    );
                }
                $navConfig[$name] = array_merge($config, array(
                    'label'         => $item['title'],
                ));
            }
            $navigation = $this->view->navigation($navConfig);
        }

        $this->side = $navigation;

        return $navigation;
    }

    /**
     * Get back-office top menu
     *
     * @param string|null $module
     * @return string
     */
    public function top($module = null)
    {
        if (null !== $this->top) {
            return $this->top;
        }

        $module = $module ?: $this->module;
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $navigation = '';
        // Managed components
        if (AdminMode::MODE_ADMIN == $mode && 'system' == $module) {
            $navConfig = Pi::registry('navigation')
                ->read('system-component') ?: array();
            $currentModule = $_SESSION['PI_BACKOFFICE']['module'];
            if ($currentModule) {
                foreach ($navConfig as $key => &$nav) {
                    $nav['params']['name'] = $currentModule;
                }
            }
            $navigation = $this->view->navigation($navConfig);
        // Module operations
        } elseif (AdminMode::MODE_ACCESS == $mode) {
            $navigation = $this->view->navigation(
                $module . '-admin',
                array('section' => 'admin')
            );
        }

        $this->top = $navigation;

        return $navigation;
    }
}
