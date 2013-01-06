<?php
/**
 * System admin theme controller
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
use Pi\Application\Installer\Theme as ThemeInstaller;

/**
 * Feature list:
 *  0. Utilization of active themes
 *  1. List of active themes
 *  2. List of themes available for installation
 *  3. List of themes in global Pi Engine repository
 *  4. Theme installation
 *  5. Theme update
 *  6. Theme uninstallation
 */
class ThemeController extends ActionController
{
    /**
     * select of themes
     */
    public function indexAction()
    {
        $section = $this->params('section', '_front');

        // Themes
        $type = ('_admin' == $section) ? 'admin' : 'front';
        $themes = Pi::service('registry')->themelist->read($type);
        foreach ($themes as $key => &$theme) {
            //$screenshot = $theme['screenshot'] ? Pi::service('asset')->getAssetUrl('theme/' . $key, $theme['screenshot'], false) : Pi::url('static/image/theme.png');
            //$theme['screenshot'] = $screenshot;
            $theme['name'] = $key;
        }

        $themeName = '';
        $moduleTheme = '';
        /*
        // Get module list
        $modules = array();
        $moduleSet = Pi::model('module')->select(array('active' => 1));
        foreach ($moduleSet as $row) {
            $modules[$row->name] = $row->title;
        }


        $moduleTheme = '';
        if (isset($modules[$section])) {
            $subject = $modules[$section];
            //$themeList = Pi::config()->loadDomain('')->get('theme_module', '');
            $themeList = Pi::config('theme_module', '');
            $themeName = !empty($themeList[$section]) ? $themeList[$section] : '';
            if (!$themeName) {
                $themeName = Pi::config('theme');
                $moduleTheme = false;
            } else {
                $moduleTheme = $themeName;
            }
        } else
        */
        if ('_admin' == $section) {
            $subject = __('admin');
            $themeName = Pi::config('theme_admin');
        } else {
            $subject = __('front');
            $themeName = Pi::config('theme');
        }
        $data = isset($themes[$themeName]) ? $themes[$themeName] : $themes['default'];
        if (isset($themes[$themeName])) {
            unset($themes[$themeName]);
        }

        $this->view()->assign('theme', $data);
        $this->view()->assign('section', $section);
        $this->view()->assign('themes', $themes);
        //$this->view()->assign('modules', $modules);
        $this->view()->assign('title', sprintf(__('Select theme for %s'), $subject));
        //$this->view()->setTemplate('theme-select');
    }

    /**
     * AJAX to apply a theme to a section
     */
    public function applyAction()
    {
        $theme = $this->params('theme');
        $section = $this->params('section');
        switch ($section) {
            case '_front':
                $name = 'theme';
                break;
            case '_admin':
                $name = 'theme_admin';
                break;
            case '_all':
            default:
                $name = 'theme_module';
                break;
        }
        $row = Pi::model('config')->select(array(
            'module'    => 'system',
            'name'      => $name,
        ))->current();
        $configValue = $row->value;
        if ('_all' == $section) {
            $configValue = array();
        } elseif ('theme_module' == $name) {
            $configValue[$section] = $theme;
        } else {
            $configValue = $theme;
        }
        $row->value = $configValue;
        $row->save();
        Pi::service('registry')->config->clear('system');

        $result = array(
            'status'    => 1,
            'message'   => __('Theme set up successfully.'),
        );
        return $result;
    }

    /**
     * List of installed themes
     */
    public function installedAction()
    {
        $themes = Pi::service('registry')->themelist->read();
        foreach ($themes as $key => &$theme) {
            //$screenshot = $theme['screenshot'] ? Pi::service('asset')->getAssetUrl('theme/' . $key, $theme['screenshot'], false) : Pi::url('static/image/theme.png');
            //$theme['screenshot'] = $screenshot;
            $theme['name'] = $key;
        }
        $this->view()->assign('themes', $themes);
        //$this->view()->setTemplate('theme-installed');
        $this->view()->assign('title', __('Installed themes'));
    }

    /**
     * List of themes available for installation
     */
    public function availableAction()
    {
        $themes = array();
        $themesInstalled = Pi::service('registry')->themelist->read();
        $iterator = new \DirectoryIterator(Pi::path('theme'));
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $directory = $fileinfo->getFilename();
            if (isset($themesInstalled[$directory]) || 'default' == $directory || preg_match('/[^a-z0-9_]/i', $directory)) {
                continue;
            }
            $meta = Pi::service('theme')->loadConfig($directory);
            if (empty($meta)) {
                continue;
            }
            $meta['name'] = $directory;
            $meta['screenshot'] = !empty($meta['screenshot']) ? Pi::url('script/browse.php') . '?' . sprintf('theme/%s/asset/%s', $directory, $meta['screenshot']) : Pi::url('static/image/theme.png');
            $themes[$directory] = $meta;
        }

        $this->view()->assign('themes', $themes);
        $this->view()->assign('title', __('Themes available for installation'));
    }

    /**
     * Update a theme, and re-publish its asset
     */
    public function updateAction()
    {
        $status = 1;
        $themeName = $this->params('name');
        $installer = new ThemeInstaller;
        $ret = $installer->update($themeName);
        $message = '';
        if (!$ret) {
            $status = 0;
            $message = $installer->renderMessage() ?: sprintf(__('The theme "%s" is not updated.'), $themeName);
        }
        $message = $message ?: sprintf(__('The theme "%s" is updated.'), $themeName);
        /*
        $this->view()->assign('message', $message);
        $this->view()->assign('title', __('Theme update'));
        $this->view()->setTemplate('theme-operation');
        */
        $themelist = Pi::service('registry')->themelist->read();
        return $themelist[$themeName];
    }

    /**
     * AJAX to install a theme and publish its asset
     */
    public function installAction()
    {
        $themeName = $this->params('name');
        $installer = new ThemeInstaller;
        $ret = $installer->install($themeName);
        $status = 1;
        $message = '';
        if (!$ret) {
            $message = $installer->renderMessage() ?: sprintf(__('The theme "%s" is not installed.'), $themeName);
            $status = 0;
        }
        $message = $message ?: sprintf(__('The theme "%s" is installed.'), $themeName);

        return array(
            'status'    => $status,
            'message'   => $message,
        );

        /*
        $this->view()->assign('message', $message);
        $this->view()->assign('title', __('Theme installation'));
        $this->view()->setTemplate('theme-operation');
        */

    }

    /**
     * AJAX to uninstall a theme and remove its asset
     */
    public function uninstallAction()
    {
        $status = 1;
        $themeName = $this->params('name');
        if ('default' == $themeName) {
            $status = 0;
            $message = __('Default theme is protected from uninstallation.');
        } else {
            $installer = new ThemeInstaller;
            $ret = $installer->uninstall($themeName);
            $message = '';
            if (!$ret) {
                $status = 0;
                $message = $installer->renderMessage() ?: sprintf(__('The theme "%s" is not uninstalled.'), $themeName);
            }
        }
        $message = $message ?: sprintf(__('The theme "%s" is uninstalled.'), $themeName);
        /*
        $this->view()->assign('message', $message);
        $this->view()->assign('title', __('Theme uninstallaton'));
        $this->view()->setTemplate('theme-operation');
        */
        $result = array(
            'status'    => $status,
            'message'   => $message,
        );

        return $result;
    }
}
