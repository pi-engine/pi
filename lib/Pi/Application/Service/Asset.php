<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Asset maintenance service
 *
 * Module asset folders/files skeleton
 *
 * - Source assts
 *   - Module native assets:
 *     - for both module "demo" and cloned "democlone":
 *      `module/demo/asset/`</li>
 *   - Module custom assets:
 *      (Note: the custom relationship is not maintained by the Asset service,
 *          it shall be addressed by module maintainer instead.)
 *     - for module "demo": `theme/default/module/demo/asset/`</li>
 *     - for module "democlone": `theme/default/module/democlone/asset/`
 *
 * - Published assts
 *   - for module "demo": `www/asset/[encrypted "module/demo"]/`
 *   - for module "democlone":  `www/asset/[encrypted "module/democlone"]/`
 *
 * Theme asset folders files skeleton
 *
 * - Source assets
 *   - `theme/default/asset/`
 *
 * - Published assets
 *   - `www/asset/<encrypted "theme/default">/`
 *
 * Other component asset folders files skeleton
 *
 * - Source assets
 *   - `path/to/component/asset/`
 *
 * - Published assets
 *   - `www/asset/<encrypted "path/to/component">/`
 *
 * @see Pi\View\Resolver\ModuleTemplate for module template skeleton
 * @see Pi\View\Resolver\ThemeTemplate for theme template skeleton
 * @see Pi\View\Resolver\ComponentTemplate for component template skeleton
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Asset extends AbstractService
{
    /**
     * Specified name for assets root folder of all components
     *
     * @var string
     */
    const DIR_ASSET = 'asset';

    /**
     * Root path of assets folder
     *
     * @var string
     */
    protected $basePath;

    /**
     * URI to assets root directory
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->useSymlink = false;
        }
    }

    /**
     * Get path to assets root folder
     *
     * @return string
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
     *
     * @return string
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
     * sha1 or md5 is good for lower collisions
     * but crc32 is good as a trade-off between collisions and hash length
     *
     * @param string $path Folder name to be hashed
     * @return string
     */
    protected function canonize($path)
    {
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
        return $this->getBasePath() . DIRECTORY_SEPARATOR
            . $this->canonize($component);
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
            $file = $this->versionStamp(
                $this->getAssetPath($component, $file),
                $file
            );
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
        $sourcePath = Pi::path($component) . DIRECTORY_SEPARATOR
                    . static::DIR_ASSET;
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
     * @param bool      $override Force to override existent files
     * @return bool
     */
    public function publishFile($sourceFile, $targetFile, $override = true)
    {
        try {
            Pi::service('file')->symlink(
                $sourceFile,
                $targetFile,
                true,
                $override
            );
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * Publishes an asset of a component,
     * only applicable for direct copy not for symbolic link
     *
     * @param string    $component component name
     * @param string    $file      file path
     * @param bool      $override Force to override existent files
     * @return bool
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
     * @param string    $component component name
     * @param string    $target target component
     * @param bool      $override Force to override existent folder:
     *  true to remove existent folder/link and to recreate it;
     *  false to overwrite file by file
     * @return bool
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
     * @return bool
     */
    public function publishCustom($theme)
    {
        $path = Pi::path('theme') . '/' . $theme . '/module';
        if (!is_dir($path)) {
            return false;
        }
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
     * @return bool
     */
    public function removeCustom($theme)
    {
        return $this->remove('custom/' . $theme);
    }

    /**
     * Remove component assets folder
     *
     * @param string $component Component name
     * @return bool
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
     * @param string $file      File path
     * @return string Full path to a static asset
     */
    public function getStaticPath($file)
    {
        return Pi::path('static') . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Gets URL of a static asset
     *
     * @param string    $file       File path
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
