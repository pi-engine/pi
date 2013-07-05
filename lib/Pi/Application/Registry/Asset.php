<?php
/**
 * Pi cache registry
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
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Pi\Application\Registry;

use Pi;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Asset extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $files = array();
        $theme = $options['theme'];
        $path = Pi::service('asset')->getPath('custom/' . $theme);
        if (is_dir($path)) {
            $iterator = new \DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isDir() && !$fileinfo->isLink() || $fileinfo->isDot()) {
                    continue;
                }
                $module = $fileinfo->getFilename();
                if (preg_match('/[^a-z0-9]+/', $module)) {
                    continue;
                }
                $modulePath = $path . '/' . $module . '/';
                $modulePathLength = strlen($modulePath);
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulePath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $fileData) {
                    if ($fileData->isFile() || $fileData->isLink()) {
                        $filePath = $fileData->getPathname();
                        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
                            $filePath = strtr($filePath, '\\', '/');
                        }
                        $filePath = substr($filePath, $modulePathLength);
                        if (preg_match('/(^[^a-z0-9\-]+|\/[^a-z0-9\-]+)/i', dirname($filePath))) {
                            continue;
                        }
                        $fileUrl = Pi::service('asset')->getCustomAsset($filePath, $module);
                        $files[$module][$filePath] = $fileUrl;
                    }
                }
            }
        }

        return $files;
    }

    public function setNamespace($meta)
    {
        if (is_string($meta)) {
            $namespace = $meta;
        } else {
            $namespace = $meta['theme'];
        }
        return parent::setNamespace($namespace);
    }

    public function read($module, $theme = null)
    {
        //$this->cache = false;
        $theme = $theme ?: Pi::service('theme')->current();
        $options = compact('theme');
        $data = $this->loadData($options);
        return isset($data[$module]) ? $data[$module] : array();
    }

    public function create($module, $theme = null)
    {
        $theme = $theme ?: Pi::service('theme')->current();
        $this->clear($theme);
        $this->read($module, $theme);
        return true;
    }

    public function flush()
    {
        $themes = Pi::service('registry')->themelist->read();
        foreach (array_keys($themes) as $theme) {
            $this->clear($theme);
        }
        return $this;
    }
}
