<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Application\Installer\Theme as ThemeInstaller;
use Zend\Json\Json;

/**
 * Theme controller
 *
 * Feature list:
 *
 *  0. Utilization of active themes
 *  1. List of active themes
 *  2. List of themes available for installation
 *  3. List of themes in global Pi Engine repository
 *  4. Theme installation
 *  5. Theme update
 *  6. Theme uninstallation
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ThemeController extends ActionController
{
    /**
     * select of themes
     */
    public function indexAction()
    {
        $section = $this->params('side', '_front');

        // Themes
        $type = ('_admin' == $section) ? 'admin' : 'front';
        $themes = Pi::registry('themelist')->read($type);
        foreach ($themes as $key => &$theme) {
            $theme['name'] = $key;
        }

        if ('_admin' == $section) {
            $subject = _a('admin');
            $themeName = Pi::config('theme_admin');
        } else {
            $subject = _a('front');
            $themeName = Pi::config('theme');
        }
        $data = isset($themes[$themeName])
            ? $themes[$themeName] : $themes['default'];
        if (isset($themes[$themeName])) {
            unset($themes[$themeName]);
        }

        $this->view()->assign('theme', $data);
        $this->view()->assign('side', $section);
        $this->view()->assign('themes', $themes);
        $this->view()->assign(
            'title',
            sprintf(_a('Select theme for %s'), $subject)
        );
    }

    /**
     * AJAX: Apply a theme to a section
     *
     * @return array
     */
    public function applyAction()
    {
        $theme = $this->params('theme');
        $section = $this->params('side');
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
        Pi::registry('config')->clear('system');

        $result = array(
            'status'    => 1,
            'message'   => _a('Theme set up successfully.'),
        );
        return $result;
    }

    /**
     * List of installed themes
     */
    public function installedAction()
    {
        $themes = Pi::registry('themelist')->read();
        foreach ($themes as $key => &$theme) {
            $theme['name'] = $key;
        }
        $this->view()->assign('themes', $themes);
        $this->view()->assign('title', _a('Installed themes'));
    }

    /**
     * List of themes available for installation
     */
    public function availableAction()
    {
        $themes = array();
        $themesInstalled = Pi::registry('themelist')->read();
        $filter = function ($fileinfo) use (&$themes, $themesInstalled) {
            if (!$fileinfo->isDir()) {
                return false;
            }
            $directory = $fileinfo->getFilename();
            if (isset($themesInstalled[$directory]) || 'default' == $directory
                || preg_match('/[^a-z0-9_]/i', $directory)
            ) {
                return false;
            }
            $meta = Pi::service('theme')->loadConfig($directory);
            if (empty($meta)) {
                return false;
            }
            $meta['name'] = $directory;
            $meta['screenshot'] = !empty($meta['screenshot'])
                ? Pi::url('script/browse.php') . '?' . sprintf(
                    'theme/%s/asset/%s',
                    $directory,
                    $meta['screenshot']
                )
                : Pi::url('static/image/theme.png');
            $themes[$directory] = $meta;
        };
        Pi::service('file')->getList('theme', $filter);

        $this->view()->assign('themes', $themes);
        $this->view()->assign(
            'title',
            _a('Themes available for installation')
        );
    }

    /**
     * AJAX: Update a theme, and re-publish its asset
     *
     * @return array
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
            $message = $installer->renderMessage()
                ?: sprintf(_a('The theme "%s" is not updated.'), $themeName);
        }
        $message = $message
            ?: sprintf(_a('The theme "%s" is updated.'), $themeName);
        $themelist = Pi::registry('themelist')->read();

        return $themelist[$themeName];
    }

    /**
     * AJAX: Install a theme and publish its asset
     *
     * @return array
     */
    public function installAction()
    {
        $themeName = $this->params('name');
        $installer = new ThemeInstaller;

        $ret = $installer->install($themeName);
        $status = 1;
        $message = '';
        if (!$ret) {
            $message = $installer->renderMessage()
                ?: sprintf(_a('The theme "%s" is not installed.'), $themeName);
            $status = 0;
        }
        $message = $message
            ?: sprintf(_a('The theme "%s" is installed.'), $themeName);

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * AJAX: Uninstall a theme and remove its asset
     *
     * @return array
     */
    public function uninstallAction()
    {
        $status = 1;
        $themeName = $this->params('name');
        if ('default' == $themeName) {
            $status = 0;
            $message = _a('Default theme is protected from uninstallation.');
        } else {
            $installer = new ThemeInstaller;
            $ret = $installer->uninstall($themeName);
            $message = '';
            if (!$ret) {
                $status = 0;
                $message = $installer->renderMessage()
                    ?: sprintf(
                        _a('The theme "%s" is not uninstalled.'),
                        $themeName
                    );
            }
        }
        $message = $message
            ?: sprintf(_a('The theme "%s" is uninstalled.'), $themeName);
        $result = array(
            'status'    => $status,
            'message'   => $message,
        );

        return $result;
    }

    /**
     * Bootstrap customization for a theme
     */
    public function customizeAction()
    {
        //http://leafo.net/lessphp/
        //require "lessc.inc.php";

        //$less = new Lessc;

        //d($less->compile(".block { padding: 3 + 4px }"));

        // Theme name to customize
        $name = $this->params('name') ?: Pi::theme()->current();

        // Lookup theme online custom bootstrap config.json
        $customFile = sprintf(
            '%s/custom/theme/%s/asset/vendor/bootstrap/config.json',
            Pi::path('asset'),
            $name
        );

        // Lookup theme specific bootstrap config.json
        if (!is_readable($customFile)) {
            $customFile = Pi::service('asset')->getAssetPath(
                'theme/' . $name,
                'vendor/bootstrap/config.json'
            );
        }

        // Lookup default bootstrap config.json
        if (!is_readable($customFile)) {
            $customFile = Pi::service('asset')->getPublicPath(
                'vendor/bootstrap/config.json'
            );
        }

        // Read bootstrap configs
        $custom = json_decode(file_get_contents($customFile), true);

        $this->view()->assign(array(
            'name'    => $name,
            'custom'  => $custom
        ));
    }

    /**
     * Compile bootstrap for a theme
     */
    public function compileAction()
    {
        // Theme name
        $name       = _post('name') ?: Pi::theme()->current();

        // Compiled boostrap.min.css, string
        $bsString   = _post('less');

        // Config JSON string
        $cfgString  = _post('custom');

        // Write bootstrap scripts to online custom theme folder
        $path = sprintf(
            '%s/custom/theme/%s/asset/vendor/bootstrap/css',
            Pi::path('asset'),
            $name
        );
        $configJson = Json::prettyPrint(json_encode($cfgString), array(
            'indent' => '  '
        ));

        Pi::service('file')->mkdir($path);
        file_put_contents($path . '/bootstrap.min.css', $bsString);
        file_put_contents(dirname($path) . '/config.json', $configJson);

        // Republish the theme
        Pi::service('asset')->publishTheme($name);

        return array(
            'status'    => 1,
            'message'   => __('Bootstrap compiled successfully.')
        );
    }

    /**
     * Reset bootstrap custom for a theme
     */
    public function resetAction()
    {
        // Theme name
        $name = _post('name') ?: Pi::theme()->current();

        // Write bootstrap scripts to online custom theme folder
        $path = sprintf(
            '%s/custom/theme/%s/asset/vendor/bootstrap',
            Pi::path('asset'),
            $name
        );
        if (is_dir($path)) {
            Pi::service('file')->remove($path);
        }

        // Republish the theme
        Pi::service('asset')->publishTheme($name);

        // Lookup theme specific bootstrap config.json
        $customFile = Pi::service('asset')->getAssetPath(
            'theme/' . $name,
            'vendor/bootstrap/config.json'
        );

        // Lookup default bootstrap config.json
        if (!is_readable($customFile)) {
            $customFile = Pi::service('asset')->getPublicPath(
                'vendor/bootstrap/config.json'
            );
        }

        // Read bootstrap configs
        $config = json_decode(file_get_contents($customFile), true);

        return array(
            'status'    => 1,
            'message'   => __('Bootstrap reset successfully.'),
            'custom'    => $config
        );
    }
}
