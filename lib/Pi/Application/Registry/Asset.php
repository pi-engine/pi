<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Asset list
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Asset extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $files = array();
        $theme = $options['theme'];
        $path = Pi::service('asset')->getPath('custom/' . $theme);
        if (is_dir($path)) {
            $iterator = new \DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isDir() && !$fileinfo->isLink()
                    || $fileinfo->isDot()
                ) {
                    continue;
                }
                $module = $fileinfo->getFilename();
                if (preg_match('/[^a-z0-9]+/', $module)) {
                    continue;
                }
                $modulePath = $path . '/' . $module . '/';
                $modulePathLength = strlen($modulePath);
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $modulePath,
                        \FilesystemIterator::SKIP_DOTS
                            | \FilesystemIterator::FOLLOW_SYMLINKS
                    ),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $fileData) {
                    if ($fileData->isFile() || $fileData->isLink()) {
                        $filePath = $fileData->getPathname();
                        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
                            $filePath = strtr($filePath, '\\', '/');
                        }
                        $filePath = substr($filePath, $modulePathLength);
                        if (preg_match(
                            '/(^[^a-z0-9\-]+|\/[^a-z0-9\-]+)/i',
                            dirname($filePath)
                        )) {
                            continue;
                        }
                        $fileUrl = Pi::service('asset')->getCustomAsset(
                            $filePath,
                            $module
                        );
                        $files[$module][$filePath] = $fileUrl;
                    }
                }
            }
        }

        return $files;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta)
    {
        if (is_string($meta)) {
            $namespace = $meta;
        } else {
            $namespace = $meta['theme'];
        }

        return parent::setNamespace($namespace);
    }

    /**
     * {@inheritDoc}
     * @param string    $module
     * @param string    $theme
     */
    public function read($module = '', $theme = '')
    {
        //$this->cache = false;
        $module = $module ?: Pi::service('module')->current();
        $theme  = $theme ?: Pi::service('theme')->current();
        $options = compact('theme');
        $data = $this->loadData($options);

        return isset($data[$module]) ? $data[$module] : array();
    }

    /**
     * {@inheritDoc}
     * @param string    $module
     * @param string    $theme
     */
    public function create($module = '', $theme = '')
    {
        $module = $module ?: Pi::service('module')->current();
        $theme  = $theme ?: Pi::service('theme')->current();
        $this->clear($theme);
        $this->read($module, $theme);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $themes = Pi::registry('themelist')->read();
        foreach (array_keys($themes) as $theme) {
            $this->clear($theme);
        }

        return $this;
    }
}
