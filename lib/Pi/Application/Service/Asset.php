<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
 * - `public`: system public resources, same domain with main application
 * - `asset`: system, module and theme public assets, allow for separate URL/domain
 *
 *
 * Module asset folders/files skeleton
 *
 * - Source assets/public resources
 *   - Module native assets:
 *     - for both module "demo" and cloned "democlone":
 *      `module/demo/asset/`
 *
 *   - Module custom assets:
 *     - for module "demo" and cloned "democlone":
 *      `custom/module/demo/asset/`
 *      `custom/module/democlone/asset/`
 *
 *
 *   - Module theme-specific assets:
 *      (Note: the custom relationship is not maintained by the Asset service,
 *          it shall be addressed by module maintainer instead.)
 *     - for module "demo": `theme/default/module/demo/asset/`
 *     - for module "democlone": `theme/default/module/democlone/asset/`
 *
 * - Published assets resources
 *   - for module "demo": `www/asset/[encrypted "module/demo"]/`
 *   - for module "democlone":  `www/asset/[encrypted "module/democlone"]/`
 *
 * Theme asset/public resource folders files skeleton
 *
 * - Source assets
 *   - `theme/default/asset/`
 *   - `theme/default/public/`
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
    //const DIR_PUBLIC = 'public';

    /**
     * Specified name for compressed asset folder
     * @var string
     */
    const DIR_BUILD = '_build';

    /** @var array List of files/directories not published or removed */
    protected $error = array();

    /**
     * Get path to assets root folder
     *
     * @return string
     */
    public function getBasePath()
    {
        $basePath = Pi::path('asset');

        return $basePath;
    }

    /**
     * Get path to assets root folder
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $baseUrl = Pi::url('asset');

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
     *
     * @return string Component assets path
     */
    public function getPath($component)
    {
        $basePath = $this->getBasePath();
        if ($component) {
            $path = $basePath . '/' . $this->canonize($component);
        } else {
            $path = $basePath;
        }

        return $path;
    }

    /**
     * Gets URL to component assets folder
     *
     * @param string $component Component name
     *
     * @return string Component assets folder URL
     */
    public function getUrl($component)
    {
        $baseUrl = $this->getBaseUrl();
        if ($component) {
            $url = $baseUrl . '/' . $this->canonize($component);
        } else {
            $url = $baseUrl;
        }

        return $url;
    }

    /**
     * Gets path of an asset
     *
     * @param string $component component name
     * @param string $file      file path
     *
     * @return string Full path to an asset
     */
    public function getAssetPath($component, $file)
    {
        return $this->getPath($component) . '/' . $file;
    }

    /**
     * Gets URL of an asset
     *
     * @param string    $component  Component name
     * @param string    $file       File path
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getAssetUrl(
        $component,
        $file,
        $appendVersion = null
    ) {
        $file = $this->versionStamp(
            $this->getAssetPath($component, $file),
            $file,
            $appendVersion
        );

        return $this->getUrl($component) . '/' . $file;
    }

    /**
     * Gets URL of an asset in current module
     *
     * @param string    $file       File path
     * @param string    $module     Module name
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getModuleAsset(
        $file,
        $module         = '',
        $appendVersion  = null
    ) {
        $module = $module ?: Pi::service('module')->current();
        $component = 'module/' . $module;

        return $this->getAssetUrl($component, $file, $appendVersion);
    }

    /**
     * Gets URL of an asset in current theme
     *
     * @param string    $file       File path
     * @param string    $theme      Theme directory
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getThemeAsset(
        $file,
        $theme          = '',
        $appendVersion  = null
    ) {
        $theme = $theme ?: Pi::service('theme')->current();
        $component = 'theme/' . $theme;

        return $this->getAssetUrl($component, $file, $appendVersion);
    }

    /**
     * Gets URL of a custom module asset in current theme
     *
     * @param string    $file       File path
     * @param string    $module
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getThemeModuleAsset(
        $file,
        $module         = '',
        $appendVersion  = null
    ) {
        $file = sprintf(
            'module/%s/%s',
            $module ?: Pi::service('module')->current(),
            $file
        );

        return $this->getThemeAsset($file, '', $appendVersion);
    }

    /**
     * Gets source path of an asset
     *
     * @param string $component     Component name
     * @param string $file          File path
     *
     * @return string Full path to an asset source
     */
    public function getSourcePath($component, $file = '')
    {
        $dir = static::DIR_ASSET;
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
     *
     * @return string Full path to an asset source
     */
    protected function getCustomPath($component, $file = '')
    {
        $component = 'custom/' . $component;
        $sourcePath = $this->getSourcePath($component, $file);

        return $sourcePath;
    }

    /**#@+
     * Resource folder and file manipulation
     */
    /**
     * Publishes component assets folder
     *
     * @param string $component     Component name
     * @param string $target        Target component
     * @param Traversable $iterator A Traversable instance for directory scan
     * @param bool $hasCustom
     *
     * @return bool
     */
    public function publish(
        $component,
        $target = '',
        Traversable $iterator = null,
        $hasCustom = false
    ) {
        // Initialize erroneous file list
        $this->setErrors();

        $result = true;
        $target = $target ?: $component;
        // Publish original assets
        $sourceFolder   = $this->getSourcePath($component);
        $targetFolder   = $this->getPath($target);
        $disableSymlink = !empty($hasCustom) ? true : false;
        $status         = $this->publishFile(
            $sourceFolder,
            $targetFolder,
            $iterator,
            $disableSymlink
        );
        if (!$status) {
            $result = $status;
            //break;
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
        // Initialize erroneous file list
        $this->setErrors();

        $result = true;
        // Publish original assets
        $component  = 'module/' . Pi::service('module')->directory($module);
        $hasCustom  = $this->hasCustom($component);
        $target     = 'module/' . $module;
        $status     = $this->publish($component, $target, null, $hasCustom);
        if (!$status) {
            $result = $status;
        }
        // Publish custom assets
        $component  = 'module/' . $module;
        $status     = $this->publishCustom($component);
        if (!$status) {
            $result = $status;
        }

        return $result;
    }

    /**
     * Publishes theme assets, including original and custom assets,
     * as well as module assets for the theme
     *
     * @param string $theme
     * @param string $target Publish to a specified theme
     *
     * @return bool
     */
    public function publishTheme($theme, $target = '')
    {
        // Initialize erroneous file list
        $this->setErrors();

        $result = true;

        $config = Pi::service('theme')->loadConfig($theme);
        $errors = array();
        if (!empty($config['parent'])) {
            $this->publishTheme($config['parent'], $theme);
            $errors = array_merge($errors, $this->getErrors());
            $this->setErrors($errors);
        }

        // Publish original assets
        $component  = 'theme/' . $theme;
        // Disable symbolic link for inherited assets
        if (!empty($target) || !empty($config['parent'])) {
            $hasCustom = true;
        } else {
            $hasCustom = $this->hasCustom($component)
                || $this->hasOnline($component)
                || $this->hasModule($component);
        }

        $targetTheme = $target ? 'theme/' . $target : '';
        $status = $this->publish($component, $targetTheme, null, $hasCustom);
        if (!$status) {
            $result = $status;
        }
        if ($target != $theme) {
            return $result;
        }

        // Publish custom assets
        $status = $this->publishCustom($component);
        if (!$status) {
            $result = $status;
        }

        // Publish online custom assets
        $status = $this->publishOnline($component);
        if (!$status) {
            $result = $status;
        }

        // Publish module assets for this theme
        $status = $this->publishThemeModule($theme);
        if (!$status) {
            $result = $status;
        }

        return $result;
    }

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
    protected function publishFile(
        $sourceFile,
        $targetFile,
        Traversable $iterator = null,
        $disableSymlink = false
    ) {
        if (!is_dir($sourceFile) && !is_link($sourceFile)) {
            return true;
        }

        $result = true;
        try {
            $copyOnWindows  = true;
            $override       = (false === $this->getOption('override')) ? false : true;
            $useSymlink     = $this->getOption('use_symlink');
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
        } catch (\Exception $e) {
            $result = false;
            $this->appendErrors(Pi::service('security')->path(sprintf(
                '%s: %s',
                $sourceFile,
                $e->getMessage()
            )));
        }

        return $result;
    }

    /**
     * Publishes custom assets
     *
     * @param string $component     Component name
     * @param string $target        Target component
     * @param string $source        Source path
     *
     * @return bool
     */
    protected function publishCustom($component, $target = '', $source = '')
    {
        $result     = true;
        $iterator   = function ($folder) {
            return new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $folder,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
        };
        $target         = $target ?: $component;
        $sourceFolder   = $source ?: $this->getSourcePath('custom/' . $component);
        if (is_dir($sourceFolder)) {
            $targetFolder   = $this->getPath($target);
            $status         = $this->publishFile(
                $sourceFolder,
                $targetFolder,
                $iterator($sourceFolder),
                true
            );
            if (!$status) {
                $result = $status;
            }
        }

        return $result;
    }

    /**
     * Publishes online custom assets
     *
     * @param string $component     Component name
     * @param string $target        Target component
     *
     * @return bool
     */
    protected function publishOnline($component, $target = '')
    {
        $sourceFolder = Pi::path('asset') . '/custom/' . $component;
        $result = $this->publishCustom($component, $target, $sourceFolder);

        return $result;
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
        $result     = true;
        $iterator   = function ($folder) {
            return new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $folder,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
        };
        $directoryIterator  = new DirectoryIterator($path);
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

            $sourceFolder = $path . '/' . $module . '/' . static::DIR_ASSET;
            if (!is_dir($sourceFolder)) {
                continue;
            }
            $targetFolder = sprintf(
                '%s/module/%s',
                $this->getPath('theme/' . $theme),
                $module
            );
            $status = $this->publishFile(
                $sourceFolder,
                $targetFolder,
                $iterator($sourceFolder),
                true
            );
            if (!$status) {
                $result = $status;
            }
        }

        return $result;
    }

    /**
     * Check if there are files in a path
     *
     * @param string $path
     *
     * @return bool
     */
    private function hasFile($path)
    {
        $result = false;
        if (is_dir($path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $path,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Check if custom assets available
     *
     * @param string $component     Component name
     *
     * @return bool
     */
    protected function hasCustom($component)
    {
        $sourceFolder   = $this->getSourcePath('custom/' . $component);
        $result         = $this->hasFile($sourceFolder);

        return $result;
    }

    /**
     * Check if online custom assets available
     *
     * @param string $component     Component name
     *
     * @return bool
     */
    protected function hasOnline($component)
    {
        $sourceFolder   = Pi::path('asset') . '/custom/' . $component;
        $result         = $this->hasFile($sourceFolder);

        return $result;
    }

    /**
     * Check if module assets available
     *
     * @param string $theme
     *
     * @return bool
     */
    protected function hasModule($theme)
    {
        $result = false;
        $path   = Pi::path('theme') . '/' . $theme . '/module';
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

            $sourceFolder = $path . '/' . $module . '/' . static::DIR_ASSET;
            $result       = $this->hasFile($sourceFolder);
            if ($result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Remove component assets folder
     *
     * @param string $component Component name
     *
     * @return bool
     */
    public function remove($component)
    {
        // Initialize erroneous file list
        $this->setErrors();

        $result = true;
        $path = $this->getPath($component);
        try {
            /*
             * @fixme The method of `flush` will remove all contents inside the path.
             *          In this case, if symlink is enabled, original contents will be removed.
             *          Disable the flush temporarily
             */
            //Pi::service('file')->flush($path);

            Pi::service('file')->remove($path);
        } catch (\Exception $e) {
            $result = false;
            $this->appendErrors(Pi::service('security')->path(sprintf(
                '%s: %s',
                $component,
                $e->getMessage()
            )));
        }

        return $result;
    }

    /**
     * Set list of erroneous files
     *
     * @param array|string $errors
     *
     * @return $this
     */
    public function setErrors($errors = array())
    {
        $this->errors = (array) $errors;

        return $this;
    }

    /**
     * Append list of erroneous file(s)
     *
     * @param array|string $errors
     *
     * @return $this
     */
    protected function appendErrors($errors = array())
    {
        $this->errors = array_merge($this->errors, (array) $errors);

        return $this;
    }

    /**
     * Get list of erroneous files
     *
     * @param bool $clearErrors Clear erroneous list after the fetch
     *
     * @return array
     */
    public function getErrors($clearErrors = true)
    {
        $errors = $this->errors;
        if ($clearErrors) {
            $this->setErrors();
        }

        return $errors;
    }
    /**#@-*/

    /**#@+
     * Static assets located in public folder
     */
    /**
     * Gets path of a public asset
     *
     * @param string $file      File path
     *
     * @return string Full path to a public asset
     */
    public function getPublicPath($file)
    {
        return Pi::path('public') . '/' . $file;
    }

    /**
     * Gets URL of a public asset
     *
     * @param string    $file       File path
     * @param bool|null $appendVersion
     *
     * @return string Full URL to the asset
     */
    public function getPublicUrl($file, $appendVersion = null)
    {
        $file = $this->versionStamp(
            $this->getPublicPath($file),
            $file,
            $appendVersion
        );

        return Pi::url('public') . '/' . $file;
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
    /**#@-*/
}
