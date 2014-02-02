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
use DirectoryIterator;
use FilesystemIterator;

/**
 * Theme-specific module asset/resource list
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ThemeModuleAsset extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $files = array();
        $appendVersion = isset($options['v']) ? $options['v'] : null;
        $theme = $options['theme'];
        $type = $options['type'];
        //$path = Pi::service('asset')->getSourcePath('theme/' . $theme . '/', $type) . '/module';
        $path = Pi::path('theme/' . $theme . '/module');
        $component = 'theme/' . $theme . '/module';
        if (is_dir($path)) {
            $iterator = new DirectoryIterator($path);
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
                $sourceFolder = Pi::service('asset')->getSourcePath(
                    $component . '/' . $module,
                    '',
                    $type
                );
                if (!is_dir($sourceFolder)) {
                    continue;
                }
                $modulePathLength = strlen($sourceFolder);

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $sourceFolder,
                        FilesystemIterator::SKIP_DOTS
                    ),
                    RecursiveIteratorIterator::LEAVES_ONLY
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
                        $fileUrl = Pi::service('asset')->getThemeModuleAsset(
                            $filePath,
                            $module,
                            $appendVersion
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
     * @param string    $type   `asset` or `public`
     * @param bool|null $appendVersion
     */
    public function read(
        $module = '',
        $theme = '',
        $type = 'asset',
        $appendVersion = null
    ) {
        //$this->cache = false;
        $module = $module ?: Pi::service('module')->current();
        $theme  = $theme ?: Pi::service('theme')->current();
        if ('public' != $type) {
            $type = 'asset';
        }
        $options = compact('theme', 'type');
        if (null !== $appendVersion) {
            $options['v'] = (bool) $appendVersion;
        }
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
        $this->read($module, $theme, 'asset');
        $this->read($module, $theme, 'public');

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
