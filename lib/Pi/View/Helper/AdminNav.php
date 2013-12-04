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
    //protected $side;

    /** @var string Top menu content */
    //protected $top;

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
     * Get back-office mode render
     *
     * @return string
     */
    public function modes($class = 'nav')
    {
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];
        $modes = Menu::modes($mode);

        $pattern =<<<'EOT'
<li class="%s%s">
    <a href="%s">
        <i class="%s"></i>
        <span class="pi-mode-text">%s</span>
    </a>
</li>
EOT;
        $content = sprintf('<ul class="%s">', $class);
        foreach ($modes as $mode) {
            $content .= sprintf(
                $pattern,
                $mode['active'] ? 'active' : '',
                $mode['link'] ? '' : 'disabled',
                $mode['link'] ? : 'javascript:void(0)',
                $mode['icon'],
                $mode['label']
            );
        }

        $content .= '</ul>';

        return $content;
    }

    /**
     * Get back-office side menu
     *
     * @return string
     */
    public function main($class = 'nav')
    {
        $module = $this->module ?: Pi::service('module')->currrent();
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $content = sprintf('<ul class="%s">', $class);
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

            $pattern =<<<'EOT'
<li class="%s">
    <a href="%s">
        <i class="%s"></i>
        <span class="pi-modules-nav-text">%s</span>
    </a>
</li>
EOT;
            foreach ($navigation as $item) {
                $content .= sprintf(
                    $pattern,
                    $item['active'] ? 'active' : '',
                    $item['href'],
                    $item['icon'] ? : 'fa fa-th',
                    $item['label']
                );
            }

        // Get operation mode navigation
        } elseif (AdminMode::MODE_ACCESS == $mode) {
            $navigation = Menu::mainOperation($module);

            $pattern =<<<'EOT'
<li class="%s">
    <a data-toggle="collapse" href="#pi-modules-nav-%s">
        <i class="%s"></i>
        <span class="pi-modules-nav-text">%s</span>
        <span class="fa fa-angle-down pi-modules-nav-director"></span>
    </a>
    <div class="%s" id="pi-modules-nav-%s" data-url="%s">
        %s
    </div>
</li>
EOT;

            foreach ($navigation as $item) {
                if ($item['active']) {
                    $sub = $this->sub('nav pi-modules-nav-sub');
                } else {
                    $sub = '';
                }
                $content .= sprintf(
                    $pattern,
                    $item['active'] ? 'active' : '',
                    $item['name'],
                    $item['icon'] ? : 'fa fa-th',
                    $item['label'],
                    $item['active'] ? 'collapse in' : 'collapse',
                    $item['name'],
                    $item['href'],
                    $item['active'] ? $sub : ''
                );
            }

        }

        $content .= '</ul>';

        return $content;
    }

    /**
     * Get back-office sub menu
     *
     * @param string $class
     *
     * @return string
     */
    public function sub($class = 'nav')
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
    public function top($class = 'nav')
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
