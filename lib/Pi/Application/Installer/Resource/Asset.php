<?php
/**
 * Pi module installer resource
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
 * @package         Pi\Application
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Pi\Application\Installer\Resource;
use Pi;

/**
 * Application resource asset maintenance
 * 1. Publish a module's assets: from a source path (module asset path) to target path (encrypted path inside www/asset)
 *    Source path: module/demo/asset; theme/default/module/demo/asset
 * 2. Remove module published assets from www/asset/[encrypted path]/
 *
 * @see Pi\Application\Service\Asset for asset maintenance
 */
class Asset extends AbstractResource
{
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

    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);

        return true;
    }

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

    public function deactivateAction()
    {
        return;
        $module = $this->event->getParam('module');
        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);
    }
}
