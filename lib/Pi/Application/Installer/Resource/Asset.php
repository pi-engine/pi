<?php
/**
 * Pi module installer resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Application resource asset maintenance
 *
 * 1. Publish a module's assets: from a source path (module asset path) to target path (encrypted path inside www/asset)
 *    - Source path: module/demo/asset; theme/default/module/demo/asset
 * 2. Remove module published assets from www/asset/[encrypted path]/
 *
 * @see Pi\Application\Service\Asset for asset maintenance
 */
class Asset extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->event->getParam('module');
        $directory = $this->event->getParam('directory');
        // Publish module native assets
        Pi::service('asset')->publish('module/' . $directory, 'module/' . $module);
        // Publish module custom assets in theme
        //Pi::service('asset')->publish('theme/' . Pi::config('theme') . '/module/' . $directory, 'module/' . $module, false);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        if ($this->skipUpgrade()) {
            return;
        }
        $module = $this->event->getParam('module');
        $directory = $this->event->getParam('directory');
        // Publish module native assets
        Pi::service('asset')->publish('module/' . $directory, 'module/' . $module);
        // Publish module custom assets in theme
        //Pi::service('asset')->publish('theme/' . Pi::config('theme') . '/module/' . $directory, 'module/' . $module, false);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        return;
        $module = $this->event->getParam('module');
        $directory = $this->event->getParam('directory');
        // Publish module native assets
        Pi::service('asset')->publish('module/' . $directory, 'module/' . $module);
        // Publish module custom assets in theme
        //Pi::service('asset')->publish('theme/' . Pi::config('theme') . '/module/' . $directory, 'module/' . $module, false);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        return;
        $module = $this->event->getParam('module');
        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);
    }
}
