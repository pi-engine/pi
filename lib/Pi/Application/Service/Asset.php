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
use Traversable;
use DirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

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
 *
 *   - Module custom assets:
 *     - for module "demo" and cloned "democlone":
 *      `custom/module/demo/asset/`
 *      `custom/module/demo/public/`
 *      `custom/module/democlone/asset/`
 *      `custom/module/democlone/public/`
 *
 *
 *   - Module theme-specific assets:
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
    public function getThemeModuleAsset(
        $file,
        $module         = '',
        $type           = 'asset',
        $appendVersion  = null
    ) {
        $file = sprintf(
            'module/%s/%s',
            $module ?: Pi::service('module')->current(),
            $file
        );

        return $this->getThemeAsset($file, '', $type, $appendVersion);
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

    /**
     * Gets custom source path of an asset
     *
     * @param string $component     Component name
     * @param string $file          File path
     * @param string $type          Type: asset, public
     *
     * @return string Full path to an asset source
     */
    protected function getCustomPath($component, $file = '', $type = 'asset')
    {
        $component = 'custom/' . $component;
        $sourcePath = $this->getSourcePath($component, $file, $type);

        return $sourcePath;
    }

    /**#@+
     * Resource folder and file manipulation
     */
    /**
     * Publishes a file
     *
     * @param string $sourceFile Source file
     * @param string $targetFile Destination
     * @param Traversable $iterator A Traversable instance for directory scan
     * @param bool $disableSymlink
     *
     * @return bool
     */
    public function publishFile(
        $sourceFile,
        $targetFile,
        Traversable $iterator = null,
        $disableSymlink = false
    ) {
        if (!is_dir($sourceFile) && !is_link($sourceFile)) {
            return true;
        }
        try {
            $copyOnWindows = true;
            $override = (false === $this->getOption('override')) ? false : true;
            $useSymlink = $this->getOption('use_symlink');
            if ($disableSymlink) {
                $useSymlink = false;
            }
            // Make hard copy
            if (false === $useSymlink) {
                Pi::service('file')->mirror(
                    $sourceFile,
                    $targetFile,
                    $iterator,
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
            trigger_error($e->getMessage());
        }

        return $status;
    }

    /**
     * Publishes component assets folder
     *
     * @param string $component     Component name
     * @param string $target        Target component
     * @param Traversable $iterator A Traversable instance for directory scan
     * @param array $hasCustom
     *
     * @return bool
     */
    public function publish(
        $component,
        $target = '',
        Traversable $iterator = null,
        array $hasCustom = array()
    ) {
        $status = true;
        $target = $target ?: $component;
        foreach (array(static::DIR_ASSET, static::DIR_PUBLIC) as $type) {
            // Publish original assets
            $sourceFolder   = $this->getSourcePath($component, '', $type);
            $targetFolder   = $this->getPath($target, $type);
            $disableSymlink = !empty($hasCustom[$type]) ? true : false;
            $status         = $this->publishFile(
                $sourceFolder,
                $targetFolder,
                $iterator,
                $disableSymlink
            );
            if (!$status) {
                //break;
            }
        }

        return $status;
    }

    /**
     * Publishes custom assets
     *
     * @param string $component     Component name
     * @param string $target        Target component
     *
     * @return bool
     */
    public function publishCustom($component, $target = '')
    {
        $status     = true;
        $iterator   = function ($folder) {
            return new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $folder,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
        };
        $target     = $target ?: $component;
        $component  = 'custom/' . $component;
        foreach (array(static::DIR_ASSET, static::DIR_PUBLIC) as $type) {
            $sourceFolder   = $this->getSourcePath($component, '', $type);
            if (!is_dir($sourceFolder)) {
                continue;
            }
            $targetFolder   = $this->getPath($target, $type);
            $status         = $this->publishFile(
                $sourceFolder,
                $targetFolder,
                $iterator($sourceFolder)
            );
            if (!$status) {
                //break;
            }
        }

        return $status;
    }

    /**
     * Check if custom assets available
     *
     * @param string $component     Component name
     *
     * @return bool[]
     */
    protected function hasCustom($component)
    {
        $result = array(
            static::DIR_ASSET   => false,
            static::DIR_PUBLIC  => false,
        );
        $component  = 'custom/' . $component;
        foreach (array(static::DIR_ASSET, static::DIR_PUBLIC) as $type) {
            $sourceFolder   = $this->getSourcePath($component, '', $type);
            if (is_dir($sourceFolder)) {
                $result[$type] = true;
            }
        }

        return $result;
    }

    /**
     * Publishes module assets, including original and custom assets
     *
     * @param string $module
     *
     * @return bool
     */
    public function publishModule($module)
    {
        // Publish original assets
        $component  = 'module/' . Pi::service('module')->directory($module);
        $hasCustom  = $this->hasCustom($component);
        $target     = 'module/' . $module;
        $status     = $this->publish($component, $target, null, $hasCustom);
        if (!$status) {
            //return $status;
        }
        // Publish custom assets
        $component  = 'module/' . $module;
        $status     = $this->publishCustom($component);

        return $status;
    }

    /**
     * Publishes theme assets, including original and custom assets,
     * as well as module assets for the theme
     *
     * @param string $theme
     *
     * @return bool
     */
    public function publishTheme($theme)
    {
        // Publish original assets
        $component  = 'theme/' . $theme;
        $hasCustom  = $this->hasCustom($component);
        $hasCustom  = $this->hasModule($component, $hasCustom);

        $status     = $this->publish($component, '', null, $hasCustom);
        if (!$status) {
            //return $status;
        }
        // Publish custom assets
        $status = $this->publishCustom($component);
        if (!$status) {
            //return $status;
        }
        // Publish module assets for this theme
        $status = $this->publishThemeModule($theme);
        if (!$status) {
            //return $status;
        }

        return $status;
    }

    /**
     * Publishes module assets in a theme
     *
     * @param string $theme
     *
     * @return bool
     */
    protected function publishThemeModule($theme)
    {
        $path = Pi::path('theme') . '/' . $theme . '/module';
        if (!is_dir($path)) {
            return true;
        }
        $status     = true;
        $iterator   = function ($folder) {
            return new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $folder,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
        };
        $component = 'theme/' . $theme;
        $directoryIterator = new DirectoryIterator($path);
        foreach ($directoryIterator as $fileinfo) {
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
                $sourceFolder = $path . '/' . $module . '/' . $type;
                if (!is_dir($sourceFolder)) {
                    continue;
                }
                $targetFolder = sprintf(
                    '%s/module/%s',
                    $this->getPath($component, $type),
                    $module
                );
                $status = $this->publishFile(
                    $sourceFolder,
                    $targetFolder,
                    $iterator($sourceFolder)
                );
                if (!$status) {
                    // break;
                }
            }
        }

        return $status;
    }

    /**
     * Check if module assets available
     *
     * @param string $theme
     * @param array $result
     *
     * @return bool[]
     */
    protected function hasModule($theme, array $result = array())
    {
        $path = Pi::path('theme') . '/' . $theme . '/module';
        if (!is_dir($path)) {
            return $result;
        }
        $directoryIterator = new DirectoryIterator($path);
        foreach ($directoryIterator as $fileinfo) {
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
                $sourceFolder = $path . '/' . $module . '/' . $type;
                if (is_dir($sourceFolder)) {
                    $result[$type] = true;
                }
            }
        }

        return $result;
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
                /*
                 * @fixme The method of `flush` will remove all contents inside the path.
                 *          In this case, if symlink is enabled, original contents will be removed.
                 *          Disable the flush temporarily
                 */
                //Pi::service('file')->flush($path);

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
