<?php
/**
 * Asset service
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
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;
use Pi;

/**
 * Asset maintenance service
 * @see \Pi\View\Resolver\ModuleTemplate for module template skeleton
 * @see \Pi\View\Resolver\ThemeTemplate for theme template skeleton
 * @see \Pi\View\Resolver\ComponentTemplate for component template skeleton
 *
 * Module asset folders/files skeleton
 * <ul>
 *  <li>
 *      <ul>Source assts
 *          <li>
 *              <ul>Module native assets:
 *                  <li>for both module "demo" and cloned "democlone"
 *                      <code>module/demo/asset/</code>
 *                  </li>
 *              </ul>
 *          </li>
 *          <li>
 *              <ul>Module custom assets: (Note: the custom relationship is not maintained by the Asset service, it shall be addressed by module maintainer instead.)
 *                  <li>for module "demo"
 *                      <code>theme/default/module/demo/asset/</code>
 *                  </li>
 *                  <li>for module "democlone"
 *                      <code>theme/default/module/democlone/asset/</code>
 *                  </li>
 *          </li>
 *      </ul>
 *  </li>
 *  <li>Published assts
 *      <code>www/asset/[encrypted "module/demo"]/</code>
 *      <code>www/asset/[encrypted "module/democlone"]/</code>
 *  </li>
 * </ul>
 *
 * Theme asset folders files skeleton
 * <ul>
 *  <li>Source assets
 *      <code>theme/default/asset/</code>
 *  </li>
 *  <li>Published assets
 *      <code>www/asset/[encrypted "theme/default"]/</code>
 *  </li>
 * </ul>
 *
 * MISC asset folders files skeleton
 * <ul>
 *  <li>Source assets
 *      <code>path/to/component/asset/</code>
 *  </li>
 *  <li>Published assets
 *      <code>www/asset/[encrypted "path/to/component"]/</code>
 *  </li>
 * </ul>
 */
class Asset extends AbstractService
{
    /**
     * Specified name for assets root folder of all components
     */
    const DIR_ASSET = 'asset';

    /**
     * Root path of assets folder
     */
    protected $basePath;

    /**
     * URI to assets root
     */
    protected $baseUrl;

    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->useSymlink = false;
        }
    }

    /**
     * Get path to assets root folder
     */
    public function getBasePath()
    {
        if (!isset($this->basePath)) {
            $this->basePath = Pi::path('asset');
        }

        return $this->basePath;
    }

    /**
     * Get path to assets root folder
     */
    public function getBaseUrl()
    {
        if (!isset($this->baseUrl)) {
            $this->baseUrl = Pi::url('asset');
        }

        return $this->baseUrl;
    }

    /**
     * Generates a canonical folder for component folder
     *
     * sha1 or md5 is good for lower collisions but crc32 is good as a trade-off between collisions and hash length
     *
     * @param string $path folder name to be hashed
     * @return string hashed string
     */
    protected function canonize($path)
    {
        //return sprintf('%x', crc32($path));
        return preg_replace('/[^a-z0-9\-]/i', '-', $path);
    }

    /**
     * Gets version of an asset
     *
     * @param string $path
     * @param string $url
     * @return string
     */
    public function versionStamp($path, $url)
    {
        $version = Pi::config('asset_versioning') ? filemtime($path) : '';
        if ($version) {
            $url .= '?' . $version;
        }
        return $url;
    }

    /**
     * Gets path to component assets folder
     *
     * @param string $component component name
     * @return string Component assets path
     */
    public function getPath($component)
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . $this->canonize($component);
    }

    /**
     * Gets URL to component assets folder
     *
     * @param string $component component name
     * @return string Component assets folder URL
     */
    public function getUrl($component)
    {
        return $this->getBaseUrl() . '/' . $this->canonize($component);
    }

    /**
     * Gets path of an asset
     *
     * @param string $component component name
     * @param string $file      file path
     * @return string Full path to an asset
     */
    public function getAssetPath($component, $file)
    {
        return $this->getPath($component) . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Gets URL of an asset
     *
     * @param string    $component  component name
     * @param string    $file       file path
     * @param bool      $versioning Flag to append version
     * @return string Full URL to the asset
     */
    public function getAssetUrl($component, $file, $versioning = true)
    {
        if ($versioning) {
            $file = $this->versionStamp($this->getAssetPath($component, $file), $file);
        }
        return $this->getUrl($component) . '/' . $file;
    }

    /**
     * Gets URL of an asset in current module
     *
     * @param string    $file      file path
     * @param string    $module    module name
     * @param bool      $versioning Flag to append version
     * @return string Full URL to the asset
     */
    public function getModuleAsset($file, $module = null, $versioning = true)
    {
        $module = $module ?: Pi::service('module')->current();
        $component = 'module/' . $module;
        return $this->getAssetUrl($component, $file, $versioning);
    }

    /**
     * Gets URL of an asset in current theme
     *
     * @param string    $file      file path
     * @param string    $theme     theme directory
     * @param bool      $versioning Flag to append version
     * @return string Full URL to the asset
     */
    public function getThemeAsset($file, $theme = null, $versioning = true)
    {
        $theme = $theme ?: Pi::service('theme')->current();
        $component = 'theme/' . $theme;
        return $this->getAssetUrl($component, $file, $versioning);
    }

    /**
     * Gets URL of a custom module asset in current theme
     *
     * @param string    $file      file path
     * @param string    $module    module name
     * @param bool      $versioning Flag to append version
     * @return string Full URL to the asset
     */
    public function getCustomAsset($file, $module = null, $versioning = true)
    {
        $module = $module ?: Pi::service('module')->current();
        $file = $module . '/' . $file;
        $theme = Pi::service('theme')->current();
        $component = 'custom/' . $theme;
        return $this->getAssetUrl($component, $file, $versioning);
    }

    /**
     * Gets source path of an asset
     *
     * @param string $component component name
     * @param string $file      file path
     * @return string Full path to an asset source
     */
    public function getSourcePath($component, $file = '')
    {
        $sourcePath = Pi::path($component) . DIRECTORY_SEPARATOR . static::DIR_ASSET;
        if (!empty($file)) {
            $sourcePath .= DIRECTORY_SEPARATOR . $file;
        }
        return $sourcePath;
    }

    /**#@+
     * Resource folder and file manipulation
     */
    /**
     * Publishes a file
     *
     * @param string    $sourceFile Source file
     * @param string    $targetFile Destination
     * @param boolean   $override Force to override existent files
     * @return boolean
     */
    public function publishFile($sourceFile, $targetFile, $override = true)
    {
        try {
            Pi::service('file')->symlink($sourceFile, $targetFile, true, $override);
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * Publishes an asset of a component, only applicable for direct copy not for symbolic link
     *
     * @param string    $component component name
     * @param string    $file      file path
     * @param boolean   $override Force to override existent files
     * @return boolean
     */
    public function publishAsset($component, $file, $override = true)
    {
        $sourceFile = $this->getSourcePath($component, $file);
        $targetFile = $this->getAssetPath($component, $file);
        return $this->publishFile($sourceFile, $targetFile, $override);
    }

    /**
     * Publishes component assets folder
     *
     * @param string $component component name
     * @param string $target target component
     * @param boolean $override Force to override existent folder: true to remove existent folder/link and to recreate it; false to overwrite file by file
     * @return boolean
     */
    public function publish($component, $target = '', $override = true)
    {
        $sourceFolder = $this->getSourcePath($component);
        $targetFolder = $this->getPath($target ?: $component);

        if (!is_dir($sourceFolder) && !is_link($sourceFolder)) {
            return true;
        }
        return $this->publishFile($sourceFolder, $targetFolder, $override);
    }

    /**
     * Publishes custom assets in a theme
     *
     * @param string $theme
     * @return boolean
     */
    public function publishCustom($theme)
    {
        $path = Pi::path('theme') . '/' . $theme . '/module';
        if (!is_dir($path)) {
            return;
        }
        $iterator = new \DirectoryIterator($path);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() && !$fileinfo->isLink() || $fileinfo->isDot()) {
                continue;
            }
            $module = $fileinfo->getFilename();
            if (preg_match('/[^a-z0-9]+/', $module)) {
                continue;
            }
            $sourcePath = $path . '/' . $module . '/asset';
            if (!is_dir($sourcePath)) {
                continue;
            }
            $targetPath = $this->getPath('custom/' . $theme) . '/' . $module;
            $this->publishFile($sourcePath, $targetPath);
        }
        return true;
    }

    /**
     * Remove custom assets in a theme
     * @param string $theme
     * @return boolean
     */
    public function removeCustom($theme)
    {
        return $this->remove('custom/' . $theme);
    }

    /**
     * Remove component assets folder
     *
     * @param string $component component name
     * @return boolean
     */
    public function remove($component)
    {
        $path = $this->getPath($component);
        try {
            Pi::service('file')->remove($path);
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }
    /**#@-*/


    /**#@+
     * Static assets located in static folder
     */
    /**
     * Gets path of a static asset
     *
     * @param string $file      file path
     * @return string Full path to a static asset
     */
    public function getStaticPath($file)
    {
        return Pi::path('static') . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Gets URL of a static asset
     *
     * @param string    $file       file path
     * @param bool      $versioning Flag to append version
     * @return string Full URL to the asset
     */
    public function getStaticUrl($file, $versioning = true)
    {
        if ($versioning) {
            $file = $this->versionStamp($this->getStaticPath($file), $file);
        }
        return Pi::url('static') . '/' . $file;
    }
}
