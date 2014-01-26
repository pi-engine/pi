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
 * Asset maintenance service:
 * - `static`: for system static resources, allow for separate URL/domain
 * - `asset`: system, module and theme public assets, allow for separate URL/domain
 * - `public`: system, module and theme public resources, same domain with main application
 *
 *
 * Module asset/public folders/files skeleton
 *
 * - Source assets/public resources
 *   - Module native assets:
 *     - for both module "demo" and cloned "democlone":
 *      `module/demo/asset/`
 *      `module/demo/public/`
 *   - Module custom assets:
 *      (Note: the custom relationship is not maintained by the Asset service,
 *          it shall be addressed by module maintainer instead.)
 *     - for module "demo": `theme/default/module/demo/asset/`
 *     - for module "democlone": `theme/default/module/democlone/asset/`
 *     - for module "demo": `theme/default/module/demo/public/`
 *     - for module "democlone": `theme/default/module/democlone/public/`
 *
 * - Published assets/public resources
 *   - for module "demo": `asset/[encrypted "module/demo"]/`
 *   - for module "democlone":  `asset/[encrypted "module/democlone"]/`
 *   - for module "demo": `www/public/[encrypted "module/demo"]/`
 *   - for module "democlone":  `www/public/[encrypted "module/democlone"]/`
 *
 * Theme asset/public resource folders files skeleton
 *
 * - Source assets
 *   - `theme/default/asset/`
 *   - `theme/default/public/`
 *
 * - Published assets
 *   - `asset/<encrypted "theme/default">/`
 *   - `www/public/<encrypted "theme/default">/`
 *
 * Other component asset folders files skeleton
 *
 * - Source assets
 *   - `path/to/component/asset/`
 *   - `path/to/component/public/`
 *
 * - Published assets
 *   - `asset/<encrypted "path/to/component">/`
 *   - `www/public/<encrypted "path/to/component">/`
 *
 * @see Pi\View\Resolver\ModuleTemplate for module template skeleton
 * @see Pi\View\Resolver\ThemeTemplate for theme template skeleton
 * @see Pi\View\Resolver\ComponentTemplate for component template skeleton
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Asset extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'asset';

    /**
     * Specified name for assets root folder of all components
     * @var string
     */
    const DIR_ASSET = 'asset';

    /**
     * Specified name for public resource root folder
     * @var string
     */
    const DIR_PUBLIC = 'public';

    /**
     * Specified name for compressed asset folder
     * @var string
     */
    const DIR_BUILD = '_build';

    /**
     * Get path to assets root folder
     *
     * @param string $type      Type: asset, public
     *
     * @return string
     */
    public function getBasePath($type = 'asset')
    {
        if ('public' == $type) {
            $basePath = Pi::path('public');// . '/' . static::DIR_PUBLIC;
        } else {
            $basePath = Pi::path('asset');
        }

        return $basePath;
    }

    /**
     * Get path to assets root folder
     *
     * @param string $type      Type: asset, public
     *
     * @return string
     */
    public function getBaseUrl($type = 'asset')
    {
        if ('public' == $type) {
            $baseUrl = Pi::url('public');// . '/' . static::DIR_PUBLIC;
        } else {
            $baseUrl = Pi::url('asset');
        }

        return $baseUrl;
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
     * Set 'append_version' option
     *
     * @param bool|null $flag
     *
     * @return bool|null
     */
    public function setAppendVersion($flag)
    {
        $result = null;
        if (null !== $flag) {
            $flag = (bool) $flag;
            if ($flag != $this->getOption('append_version')) {
                $result = $this->getOption('append_version');
                $this->setOption('append_version', $flag);
            }
        }

        return $result;
    }

    /**
     * Gets version of an asset
     *
     * @param string $path
     * @param string $url
     * @param bool|null $appendVersion
     *
     * @return string
     */
    public function versionStamp($path, $url, $appendVersion = null)
    {
        $appendVersion = (null !== $appendVersion)
            ? (bool) $appendVersion
            : (bool) $this->getOption('append_version');
        $version = $appendVersion ? filemtime($path) : '';
        if ($version) {
            $url .= '?' . $version;
        }

        return $url;
    }

    /**
     * Gets path to component assets folder
     *
     * @param string $component Component name
     * @param string $type      Type: asset, public
     *
     * @return string Component assets path
     */
    public function getPath($component, $type = 'asset')
    {
        $basePath = $this->getBasePath($type);

        return $basePath . '/' . $this->canonize($component);
    }

    /**
     * Gets URL to component assets folder
     *
     * @param string $component Component name
     * @param string $type      Type: asset, public
     *
     * @return string Component assets folder URL
     */
    public function getUrl($component, $type = 'asset')
    {
        $baseUrl = $this->getBaseUrl($type);

        return $baseUrl . '/' . $this->canonize($component);
    }

    /**
     * Gets path of an asset
     *
     * @param string $component component name
     * @param string $file      file path
     * @param string $type      Type: asset, public
     *
     * @return string Full path to an asset
     */
    public function getAssetPath($component, $file, $type = 'asset')
    {
        return $this->getPath($component, $type) . '/' . $file;
    }

    /**
     * Gets URL of an asset
     *
     * @param string    $component  Component name
     * @param string    $file       File path
     * @param string    $type       Type: asset, public
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getAssetUrl(
        $component,
        $file,
        $type = 'asset',
        $appendVersion = null
    ) {
        $file = $this->versionStamp(
            $this->getAssetPath($component, $file, $type),
            $file,
            $appendVersion
        );

        return $this->getUrl($component, $type) . '/' . $file;
    }

    /**
     * Gets URL of an asset in current module
     *
     * @param string    $file       File path
     * @param string    $module     Module name
     * @param string    $type       Type: asset, public
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getModuleAsset(
        $file,
        $module         = '',
        $type           = 'asset',
        $appendVersion  = null
    ) {
        $module = $module ?: Pi::service('module')->current();
        $component = 'module/' . $module;

        return $this->getAssetUrl($component, $file, $type, $appendVersion);
    }

    /**
     * Gets URL of an asset in current theme
     *
     * @param string    $file       File path
     * @param string    $theme      Theme directory
     * @param string    $type       Type: asset, public
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getThemeAsset(
        $file,
        $theme          = '',
        $type           = 'asset',
        $appendVersion  = null
    ) {
        $theme = $theme ?: Pi::service('theme')->current();
        $component = 'theme/' . $theme;

        return $this->getAssetUrl($component, $file, $type, $appendVersion);
    }

    /**
     * Gets URL of a custom module asset in current theme
     *
     * @param string    $file       File path
     * @param string    $module
     * @param string    $type       Type: asset, public
     * @param bool|null $appendVersion
     *
     * @internal param string $theme Theme directory
     * @return string Full URL to the asset
     */
    public function getCustomAsset(
        $file,
        $module         = '',
        $type           = 'asset',
        $appendVersion  = null
    ) {
        $module = $module ?: Pi::service('module')->current();
        $file = $module . '/' . $file;
        $theme = Pi::service('theme')->current();
        $component = 'custom/' . $theme;

        return $this->getAssetUrl($component, $file, $type, $appendVersion);
    }

    /**
     * Gets source path of an asset
     *
     * @param string $component     Component name
     * @param string $file          File path
     * @param string $type          Type: asset, public
     *
     * @return string Full path to an asset source
     */
    public function getSourcePath($component, $file = '', $type = 'asset')
    {
        $dir = ('public' == $type) ? static::DIR_PUBLIC : static::DIR_ASSET;
        $sourcePath = Pi::path($component) . '/' . $dir;
        if (is_dir($sourcePath . '/' . static::DIR_BUILD)) {
            $sourcePath .= '/' . static::DIR_BUILD;
        }
        if (!empty($file)) {
            $sourcePath .= '/' . $file;
        }

        return $sourcePath;
    }

    /**#@+
     * Resource folder and file manipulation
     */
    /**
     * Publishes a file
     *
     * @param string    $sourceFile     Source file
     * @param string    $targetFile     Destination
     *
     * @return bool
     */
    public function publishFile($sourceFile, $targetFile)
    {
        try {
            $copyOnWindows = true;
            $override = (false === $this->getOption('override')) ? false : true;
            // Make hard copy
            if (false === $this->getOption('use_symlink')) {
                Pi::service('file')->mirror(
                    $sourceFile,
                    $targetFile,
                    null,
                    array(
                        'copy_on_windows'   => $copyOnWindows,
                        'override'          => $override,
                    )
                );

            // Use symlink for performance consideration
            } else {
                Pi::service('file')->symlink(
                    $sourceFile,
                    $targetFile,
                    $copyOnWindows,
                    $override
                );
            }

            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * Publishes an asset file of a component,
     * only applicable for direct copy not for symbolic link
     *
     * @param string $component     Component name
     * @param string $file          File path
     * @param string $type          Type: asset, public
     *
     * @return bool
     */
    public function publishAsset($component, $file, $type = 'asset')
    {
        $sourceFile = $this->getSourcePath($component, $file, $type);
        $targetFile = $this->getAssetPath($component, $file, $type);

        return $this->publishFile($sourceFile, $targetFile);
    }

    /**
     * Publishes component assets folder
     *
     * @param string $component     Component name
     * @param string $target        Target component
     *
     * @return bool
     */
    public function publish($component, $target = '')
    {
        foreach (array(static::DIR_ASSET, static::DIR_PUBLIC) as $type) {
            $sourceFolder = $this->getSourcePath($component, '', $type);
            $targetFolder = $this->getPath($target ?: $component, $type);
            if (!is_dir($sourceFolder) && !is_link($sourceFolder)) {
                continue;
            }
            $this->publishFile($sourceFolder, $targetFolder, $type);
        }

        return true;
    }

    /**
     * Publishes custom assets in a theme
     *
     * @param string $theme
     *
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
            foreach (array(static::DIR_ASSET, static::DIR_PUBLIC) as $type) {
                $sourcePath = $path . '/' . $module . '/' . $type;
                if (!is_dir($sourcePath)) {
                    continue;
                }
                $targetPath = $this->getPath('custom/' . $theme, $type)
                            . '/' . $module;
                $this->publishFile($sourcePath, $targetPath);
            }
        }

        return true;
    }

    /**
     * Remove custom assets in a theme
     *
     * @param string $theme
     *
     * @return bool
     */
    public function removeCustom($theme)
    {
        foreach (array(static::DIR_ASSET, static::DIR_PUBLIC) as $type) {
            $this->remove('custom/' . $theme, $type);
        }

        return true;
    }

    /**
     * Remove component assets folder
     *
     * @param string $component Component name
     * @param string $type      Type: asset, public
     *
     * @return bool
     */
    public function remove($component, $type = '')
    {
        $status = true;
        if (!$type) {
            foreach (array(static::DIR_ASSET, static::DIR_PUBLIC) as $type) {
                $this->remove($component, $type);
            }
        } else {
            $path = $this->getPath($component, $type);
            try {
                Pi::service('file')->remove($path);
                $status = true;
            } catch (\Exception $e) {
                $status = false;
            }
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
        return Pi::path('static') . '/' . $file;
    }

    /**
     * Gets URL of a static asset
     *
     * @param string    $file       File path
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getStaticUrl($file, $appendVersion = null)
    {
        $file = $this->versionStamp(
            $this->getStaticPath($file),
            $file,
            $appendVersion
        );

        return Pi::url('static') . '/' . $file;
    }
}
