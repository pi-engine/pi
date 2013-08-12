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
        $this->view()->assign('title', __('Asset component list'));
        //$this->view()->setTemplate('asset-list');
    }

    /**
     * Publish assets of a comoponent
     *
     * @return array Result pair of status and message
     */
    public function publishAction()
    {
        $type = $this->params('type');
        $name = $this->params('name');
        if ('module' == $type) {
            $source = sprintf(
                '%s/%s',
                $type,
                Pi::service('module')->directory($name)
            );
        } else {
            $source = sprintf('%s/%s', $type, $name);
        }
        $target = sprintf('%s/%s', $type, $name);
        $status = (int) Pi::service('asset')->publish($source, $target);
        if ($status && 'module' != $type) {
            Pi::service('asset')->removeCustom($name);
            Pi::service('asset')->publishCustom($name);
        }
        if (!$status) {
            $message = __('Asset files are not published correctly, please copy asset files manually.');
        } else {
            $message = __('Asset files are published correctly.');
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

        $assetList = array();
        $assetCustom = array();
        foreach ($modules as $name => $item) {
            $assetList['module-' . $name] = array(
                'source'    => 'module/' . $item['directory'],
                'title'     => sprintf(__('module %s'), $item['title']),
            );
        }
        foreach ($themes as $name => $item) {
            $assetList['theme-' . $name] = array(
                'source'    => 'theme/' . $name,
                'title'     => sprintf(__('theme %s'), $item['title']),
            );
            $assetCustom[] = $name;
        }

        // Remove deprecated components
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

        // Republish all module/theme components
        $erroneous = array();
        foreach ($assetList as $target => $item) {
            $target = str_replace('-', '/', $target);
            $status = Pi::service('asset')->publish($item['source'], $target);
            if (!$status) {
                $erroneous[] = $item['title'];
            }
        }
        foreach ($assetCustom as $name) {
            Pi::service('asset')->removeCustom($name);
            Pi::service('asset')->publishCustom($name);
        }

        if ($erroneous) {
            $status = 0;
            $message = sprintf(
                __('There are errors with: %s.'),
                implode(' | ', $erroneous)
            );
        } else {
            $status = 1;
            $message = __('Assets re-published successfully.');
        }

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }
}
