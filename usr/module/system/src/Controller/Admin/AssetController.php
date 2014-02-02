<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Asset admin controller
 *
 * Feature list:
 *
 *  - List of asset folders
 *  - Publish a component's asset
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AssetController extends ActionController
{
    /**
     * List of assets
     *
     * @return void
     */
    public function indexAction()
    {
        // Get module list
        $modules = array();
        $rowset = Pi::model('module')->select(array('active' => 1));
        foreach ($rowset as $row) {
            $modules[$row->name] = $row->title;
        }

        // Get theme list
        $themes = array();
        $rowset = Pi::model('theme')->select(array());
        foreach ($rowset as $row) {
            $themes[$row->name] = $row->name;
        }

        $this->view()->assign('modules', $modules);
        $this->view()->assign('themes', $themes);
        $this->view()->assign('title', _a('Asset component list'));
        //$this->view()->setTemplate('asset-list');
    }

    /**
     * Publish assets of a component
     *
     * @return array Result pair of status and message
     */
    public function publishAction()
    {
        $type = $this->params('type', 'module');
        $name = $this->params('name');
        switch ($type) {
            case 'module':
                $status = Pi::service('asset')->remove('module/' . $name);
                $status = Pi::service('asset')->publishModule($name);
                break;
            case 'theme':
                $status = Pi::service('asset')->remove('theme/' . $name);
                $status = Pi::service('asset')->publishTheme($name);
                break;
            default:
                $component = sprintf('%s/%s', $type, $name);
                $status = Pi::service('asset')->remove($component);
                $status = Pi::service('asset')->publish($component);
                break;
        }
        clearstatcache();
        if (!$status) {
            $message = _a('Asset files are not published correctly, please copy asset files manually.');
        } else {
            $message = _a('Asset files are published correctly.');
        }

        //$this->redirect()->toRoute('', array('action' => 'index'));
        //return;

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * Refresh assets of all modules and themes
     *
     * @return array Result pair of status and message
     */
    public function refreshAction()
    {
        $modules = Pi::registry('module')->read();
        $themes = Pi::registry('theme')->read();

        /*
        $assetList = array();
        $assetCustom = array();
        foreach ($modules as $name => $item) {
            $moduleList[$name] = array(
                //'source'    => 'module/' . $item['directory'],
                'title'     => sprintf(_a('module %s'), $item['title']),
            );
        }
        foreach ($themes as $name => $item) {
            $themeList[$name] = array(
                'source'    => 'theme/' . $name,
                'title'     => sprintf(_a('theme %s'), $item['title']),
            );
            //$assetCustom[] = $name;
        }

        // Remove published module and theme assets
        $iterator = new \DirectoryIterator(Pi::path('asset'));
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $directory = $fileinfo->getFilename();
            if (('module-' == substr($directory, 0, 7)
                || 'theme-' == substr($directory, 0, 6))
                && !isset($assetList[$directory])
            ) {
                $component = str_replace('-', '/', $directory);
                Pi::service('asset')->remove($component);
            }
        }

        $iterator = new \DirectoryIterator(Pi::path('public'));
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $directory = $fileinfo->getFilename();
            if (('module-' == substr($directory, 0, 7)
                    || 'theme-' == substr($directory, 0, 6))
                && !isset($assetList[$directory])
            ) {
                $component = str_replace('-', '/', $directory);
                Pi::service('asset')->remove($component);
            }
        }
        */

        // Republish all module/theme components
        $erroneous = array();
        foreach (array_keys($modules) as $name) {
            $status = Pi::service('asset')->remove('module/' . $name);
            if (!$status) {
                $erroneous[] = 'module-remove-' . $name;
            }
            $status = Pi::service('asset')->publishModule($name);
            if (!$status) {
                $erroneous[] = 'module-publish-' . $name;
            }
        }
        foreach (array_keys($themes) as $name) {
            $status = Pi::service('asset')->remove('theme/' . $name);
            if (!$status) {
                $erroneous[] = 'theme-remove-' . $name;
            }
            $status = Pi::service('asset')->publishTheme($name);
            if (!$status) {
                $erroneous[] = 'theme-publish-' . $name;
            }
        }
        clearstatcache();

        if ($erroneous) {
            $status = 0;
            $message = sprintf(
                _a('There are errors with: %s.'),
                implode(' | ', $erroneous)
            );
        } else {
            $status = 1;
            $message = _a('Assets re-published successfully.');
        }

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }
}
