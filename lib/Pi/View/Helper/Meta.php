<?php
/**
 * Theme meta helper
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
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Renderer\RendererInterface as Renderer;

/**
 * Helper for reading meta data
 *
 * Usage inside a phtml template:
 * <code>
 *  $meta = $this->meta();
 *  $this->meta('footer');
 * </code>
 */
class Meta extends AbstractHelper
{
    /**
     * Meta data
     * @var array
     */
    protected $metaData;

    /**
     * Get meta data
     *
     * @param   string|null  $name
     * @return  string|Meta
     */
    public function __invoke($name = null)
    {
        if (!$name) {
            return $this;
        }
        return $this->getMeta($name);
    }

    /**
     * Get meta from configuration
     *
     * @param string $name
     * @return mixed
     */
    public function getMeta($name)
    {
        $config = Pi::service('registry')->config->read('system', 'meta');
        return isset($config[$name]) ? $config[$name] : null;
    }

    /**
     * Get general config from configuration
     *
     * @param string $name
     * @return mixed
     */
    public function getConfig($name)
    {
        $config = Pi::service('registry')->config->read('system');
        return isset($config[$name]) ? $config[$name] : null;
    }

    /**
     * Assign meta data to root view model and initialize views
     */
    public function assign()
    {
        $view = $this->getView();

        // Load meta config
        $configMeta = Pi::service('registry')->config->read('system', 'meta');
        // Set head meta
        foreach ($configMeta as $key => $value) {
            if (!$value) {
                continue;
            }
            $view->headMeta()->appendName($key, $value);
        }

        // Load general config
        $configGeneral = Pi::service('registry')->config->read('system');
        // Set Google Analytics scripts in case available
        if ($configGeneral['ga_account']) {
            $view->footScript()->appendScript($view->ga($configGeneral['ga_account']));
        }
        // Set foot scripts in case available
        if ($configGeneral['foot_script']) {
            if (false !== stripos($configGeneral['foot_script'], '<script ')) {
                $view->footScript()->appendScript($configGeneral['foot_script'], 'raw');
            } else {
                $view->footScript()->appendScript($configGeneral['foot_script']);
            }
        }
        unset($configGeneral['ga_account'], $configGeneral['foot_script']);

        // Set global variables to root ViewModel
        $rootModel = $view->plugin('view_model')->getRoot();
        $rootModel->setVariables($configGeneral);

        // Set page title
        $view->headTitle()->setSeparator(' - ');
        $view->headTitle()->append($configGeneral['sitename']);
        $view->headTitle()->append($configGeneral['slogan']);

        // Set default styles
        /*
        $view->headLink()->appendStylesheet($view->assetTheme('css/style.css'))
                ->appendStylesheet($view->assetTheme('css/' . $configGeneral['locale'] . '.css'));
        */
    }
}
