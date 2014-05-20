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
use ArrayObject;
use Closure;
use Exception;
use Traversable;
use DirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Pi\File\Transfer\Upload;
use Pi\File\Transfer\Download;

/**
 * Filesystem manipulation service
 *
 * Provides basic utility to manipulate the file system.
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Filesystem/Filesystem.php
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class File extends AbstractService
{
    /**
     * Upload files
     *
     * @param array $options
     * @param bool $doUpload
     *
     * @return Upload
     * @see Pi\File\Upload
     */
    public function upload(array $options = array(), $doUpload = false)
    {
        $uploader = new Upload($options);
        if ($doUpload && $uploader->isValid()) {
            $uploader->receive();
        }

        return $uploader;
    }

    /**
     * Send the file to the client (Download)
     *
     * @param string|array $source File or file meta to download
     * @param array $options Options for the file(s) to send
     *
     * @return Download
     * @see Pi\File\Download
     */
    public function download($source, array $options = array())
    {
        $downloader = new Download;
        $downloader->send($source, $options);

        return $downloader;
    }

    /**
     * Transform file size
     *
     * @param int|string $value
     *
     * @return string|int|bool
     */
    public function transformSize($value)
    {
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        if (is_numeric($value)) {
            $value = (int) $value;
            for ($i = 0; $value >= 1024 && $i < 9; $i++) {
                $value /= 1024;
            }

            $result = round($value, 2) . $sizes[$i];
        } else {
            $value = trim($value);
            $pattern = '/^([0-9]+)[\s]?(' . implode('|', $sizes). ')$/i';
            if (preg_match($pattern, $value, $matches)) {
                $value = (int) $matches[1];
                $unit = strtoupper($matches[2]);
                $idx = array_search($unit, $sizes);
                if (false !== $idx) {
                    $result = $value * pow(1024, $idx);
                }
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Copies a file.
     *
     * This method only copies the file if the origin file is newer
     * than the target file.
     *
     * By default, if the target already exists, it is not overridden.
     *
     * @param string    $originFile The original filename
     * @param string    $targetFile The target filename
     * @param bool      $override   Whether to override an existing file
     * @return $this
     *
     * @throws Exception When copy fails
     */
    public function copy($originFile, $targetFile, $override = false)
    {
        $this->mkdir(dirname($targetFile));

        if (!$override && is_file($targetFile)) {
            $doCopy = filemtime($originFile) > filemtime($targetFile);
        } else {
            $doCopy = true;
        }

        if ($doCopy) {
            if (true !== @copy($originFile, $targetFile)) {
                throw new Exception(sprintf(
                    'Failed to copy %s to %s',
                    $originFile,
                    $targetFile
                ));
            }
        }

        return $this;
    }

    /**
     * Creates a directory recursively.
     *
     * @param string|array|Traversable  $dirs The directory path
     * @param int                       $mode The directory mode
     * @return $this
     *
     * @throws Exception On any directory creation failure
     */
    public function mkdir($dirs, $mode = 0777)
    {
        foreach ($this->toIterator($dirs) as $dir) {
            if (is_dir($dir)) {
                continue;
            }

            if (true !== @mkdir($dir, $mode, true)) {
                throw new Exception(sprintf('Failed to create %s', $dir));
            }
        }

        return $this;
    }

    /**
     * Checks the existence of files or directories.
     *
     * @param string|array|Traversable $files A filename,
     *      an array of files, or a Traversable instance to check
     *
     * @return Bool
     */
    public function exists($files)
    {
        foreach ($this->toIterator($files) as $file) {
            if (!file_exists($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string|array|Traversable $files
     *      A filename, an array of files, or a Traversable instance to create
     * @param int                       $time
     *      The touch time as a unix timestamp
     * @param int                       $atime
     *      The access time as a unix timestamp
     * @return $this
     *
     * @throws Exception When touch fails
     */
    public function touch($files, $time = null, $atime = null)
    {
        if (null === $time) {
            $time = time();
        }

        foreach ($this->toIterator($files) as $file) {
            if (true !== @touch($file, $time, $atime)) {
                throw new Exception(sprintf('Failed to touch %s', $file));
            }
        }

        return $this;
    }

    /**
     * Empties directories.
     *
     * @param string|array|Traversable $dirs The directory path
     * @return $this
     */
    public function flush($dirs)
    {
        $dirs = iterator_to_array($this->toIterator($dirs));
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $this->remove(new FilesystemIterator($dir));
        }

        return $this;
    }

    /**
     * Removes files or directories.
     *
     * @param string|array|Traversable $files
     *      A filename, an array of files, or a Traversable instance to remove
     * @return $this
     *
     * @throws Exception When removal fails
     */
    public function remove($files)
    {
        $files = iterator_to_array($this->toIterator($files));
        $files = array_reverse($files);
        foreach ($files as $file) {
            if (!file_exists($file) && !is_link($file)) {
                continue;
            }

            // hard directory
            if (is_dir($file) && !is_link($file)) {
                $this->remove(new FilesystemIterator($file));

                if (true !== @rmdir($file)) {
                    throw new Exception(
                        sprintf('Failed to remove directory %s', $file)
                    );
                }
            } else {
                // symbolic directory on Windows
                // https://bugs.php.net/bug.php?id=52176
                if (defined('PHP_WINDOWS_VERSION_MAJOR') && is_dir($file)) {
                    if (true !== @rmdir($file)) {
                        throw new Exception(
                            sprintf('Failed to remove file %s', $file)
                        );
                    }
                // symbolic directory or file
                } else {
                    if (true !== @unlink($file)) {
                        throw new Exception(
                            sprintf('Failed to remove file %s', $file)
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Change mode for an array of files or directories.
     *
     * @param string|array|Traversable $files
     *      A filename, an array of files,
     *      or a Traversable instance to change mode
     * @param int                       $mode      The new mode (octal)
     * @param int                       $umask     The mode mask (octal)
     * @param Bool                      $recursive
     *      Whether change the mod recursively or not
     * @return $this
     *
     * @throws Exception When the change fail
     */
    public function chmod($files, $mode, $umask = 0000, $recursive = false)
    {
        foreach ($this->toIterator($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chmod(
                    new FilesystemIterator($file),
                    $mode,
                    $umask,
                    true
                );
            }
            if (true !== @chmod($file, $mode & ~$umask)) {
                throw new Exception(sprintf('Failed to chmod file %s', $file));
            }
        }

        return $this;
    }

    /**
     * Change the owner of an array of files or directories
     *
     * @param string|array|Traversable $files
     *      A filename, an array of files,
     *      or a \Traversable instance to change owner
     * @param string                    $user      The new owner user name
     * @param Bool                      $recursive
     *      Whether change the owner recursively or not
     * @return $this
     *
     * @throws Exception When the change fail
     */
    public function chown($files, $user, $recursive = false)
    {
        foreach ($this->toIterator($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chown(new FilesystemIterator($file), $user, true);
            }
            if (is_link($file) && function_exists('lchown')) {
                if (true !== @lchown($file, $user)) {
                    throw new Exception(
                        sprintf('Failed to chown file %s', $file)
                    );
                }
            } else {
                if (true !== @chown($file, $user)) {
                    throw new Exception(
                        sprintf('Failed to chown file %s', $file)
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Change the group of an array of files or directories
     *
     * @param string|array|Traversable $files
     *      A filename, an array of files,
     *      or a Traversable instance to change group
     * @param string                    $group     The group name
     * @param Bool                      $recursive
     *      Whether change the group recursively or not
     * @return $this
     *
     * @throws Exception When the change fail
     */
    public function chgrp($files, $group, $recursive = false)
    {
        foreach ($this->toIterator($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chgrp(new FilesystemIterator($file), $group, true);
            }
            if (is_link($file) && function_exists('lchgrp')) {
                if (true !== @lchgrp($file, $group)) {
                    throw new Exception(
                        sprintf('Failed to chgrp file %s', $file)
                    );
                }
            } else {
                if (true !== @chgrp($file, $group)) {
                    throw new Exception(
                        sprintf('Failed to chgrp file %s', $file)
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Renames a file.
     *
     * @param string $origin The origin filename
     * @param string $target The new filename
     * @return $this
     *
     * @throws Exception When target file already exists
     * @throws Exception When origin cannot be renamed
     */
    public function rename($origin, $target)
    {
        // we check that target does not exist
        if (is_readable($target)) {
            throw new Exception(sprintf(
                'Cannot rename because the target "%s" already exist.',
                $target
            ));
        }

        if (true !== @rename($origin, $target)) {
            throw new Exception(
                sprintf('Cannot rename "%s" to "%s".', $origin, $target)
            );
        }

        return $this;
    }

    /**
     * Creates a symbolic link or copy a directory.
     *
     * @param string    $originDir     The origin directory path
     * @param string    $targetDir     The symbolic link name
     * @param Bool      $copyOnWindows Whether to copy files if on Windows
     * @param Bool      $override
     *      Whether to override existing files
     * @return $this
     *
     * @throws Exception When symlink fails
     */
    public function symlink(
        $originDir,
        $targetDir,
        $copyOnWindows = true,
        $override = false
    ) {
        if (!function_exists('symlink')
            || (defined('PHP_WINDOWS_VERSION_MAJOR') && $copyOnWindows)
        ) {
            $this->mirror($originDir, $targetDir, null, array(
                'copy_on_windows'   => $copyOnWindows,
                'override'          => $override,
            ));

            return $this;
        }

        $this->mkdir(dirname($targetDir));

        $ok = false;
        if (is_link($targetDir)) {
            if ($override || readlink($targetDir) != $originDir) {
                $this->remove($targetDir);
            } else {
                $ok = true;
            }
        }

        if (!$ok) {
            if (true !== symlink($originDir, $targetDir)) {
                $report = error_get_last();
                if (is_array($report)) {
                    if (defined('PHP_WINDOWS_VERSION_MAJOR')
                        && false !== strpos(
                            $report['message'],
                            'error code(1314)'
                        )
                    ) {
                        throw new Exception(
                            'Unable to create symlink due to error code 1314: '
                            . '\'A required privilege is not held '
                            . 'by the client\'. '
                            . 'Do you have the required Administrator-rights?'
                        );
                    }
                }
                throw new Exception(sprintf(
                    'Failed to create symbolic link from %s to %s',
                    $originDir,
                    $targetDir
                ));
            }
        }

        return $this;
    }

    /**
     * Given an existing path,
     * convert it to a path relative to a given starting path
     *
     * @param string $endPath   Absolute path of target
     * @param string $startPath Absolute path where traversal begins
     *
     * @return string Path of target relative to starting path
     */
    public function makePathRelative($endPath, $startPath)
    {
        // Normalize separators on windows
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $endPath = strtr($endPath, '\\', '/');
            $startPath = strtr($startPath, '\\', '/');
        }

        // Split the paths into arrays
        $startPathArr = explode('/', trim($startPath, '/'));
        $endPathArr = explode('/', trim($endPath, '/'));

        // Find for which directory the common path stops
        $index = 0;
        while (isset($startPathArr[$index])
               && isset($endPathArr[$index])
               && $startPathArr[$index] === $endPathArr[$index]
        ) {
            $index++;
        }

        // Determine how deep the start path is relative to the common path
        // (ie, "web/bundles" = 2 levels)
        $depth = count($startPathArr) - $index;

        // Repeated "../" for each level need to reach the common path
        $traverser = str_repeat('../', $depth);

        $endPathRemainder = implode('/', array_slice($endPathArr, $index));

        // Construct $endPath from traversing to the common path,
        // then to the remaining $endPath
        $relativePath = $traverser
                      . (strlen($endPathRemainder) > 0
                         ? $endPathRemainder . '/' : '');

        return (strlen($relativePath) === 0) ? './' : $relativePath;
    }

    /**
     * Mirrors a directory to another.
     *
     * With options
     *
     *  - override: Whether to override an existing file on copy
     *      {@see copy()};
     *  - copy_on_windows: Whether to copy files instead of links on Windows
     *      {@see symlink()}.
     *
     * @param string        $originDir The origin directory
     * @param string        $targetDir The target directory
     * @param Traversable   $iterator  A Traversable instance
     * @param array         $options   An array of bool options
     * @return $this
     *
     * @throws Exception When file type is unknown
     */
    public function mirror(
        $originDir,
        $targetDir,
        Traversable $iterator = null,
        $options = array()
    ) {
        $copyOnWindows = true;
        if (isset($options['copy_on_windows'])
            && defined('PHP_WINDOWS_VERSION_MAJOR')
        ) {
            $copyOnWindows = $options['copy_on_windows'];
        }

        if (null === $iterator) {
            $flags = $copyOnWindows
                ? FilesystemIterator::SKIP_DOTS
                    | FilesystemIterator::FOLLOW_SYMLINKS
                : FilesystemIterator::SKIP_DOTS;
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($originDir, $flags),
                RecursiveIteratorIterator::SELF_FIRST
            );
        }

        $targetDir = rtrim($targetDir, '/\\');
        $originDir = rtrim($originDir, '/\\');

        foreach ($iterator as $file) {
            $target = str_replace(
                $originDir,
                $targetDir,
                $file->getPathname()
            );

            if (is_dir($file)) {
                $this->mkdir($target);
            } elseif (!$copyOnWindows && is_link($file)) {
                $this->symlink($file, $target);
            } elseif (is_file($file) || ($copyOnWindows && is_link($file))) {
                $this->copy(
                    $file,
                    $target,
                    isset($options['override']) ? $options['override'] : false
                );
            } else {
                throw new Exception(
                    sprintf('Unable to guess "%s" file type.', $file)
                );
            }
        }

        return $this;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return Bool
     */
    public function isAbsolutePath($file)
    {
        //$result = preg_match('|^([a-zA-Z]:)?/|', $path);
        $result = false;
        if (strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && substr($file, 1, 1) === ':'
                && (strspn($file, '/\\', 2, 1))
               )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * Transform array to iterator
     *
     * @param mixed $files
     * @return Traversable
     */
    protected function toIterator($files)
    {
        if (!$files instanceof Traversable) {
            $files = new ArrayObject(is_array($files)
                ? $files : array($files));
        }

        return $files;
    }

    /**
     * Get file list in a directory
     *
     * @param DirectoryIterator|string  $path
     * @param Closure|null              $filter
     * @param bool                      $recursive
     *
     * @return array
     */
    public function getList($path, $filter = null, $recursive = false)
    {
        $result = array();
        if ($path instanceof DirectoryIterator) {
            $iterator = $path;
        } else {
            $path = $this->isAbsolutePath($path) ? $path : Pi::path($path);
            if ($recursive) {
                $flags = FilesystemIterator::SKIP_DOTS
                    | FilesystemIterator::FOLLOW_SYMLINKS
                    | FilesystemIterator::UNIX_PATHS;
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, $flags)
                );
            } else {
                $iterator = new DirectoryIterator($path);
            }
        }
        $filter = $filter instanceof Closure ? $filter : function ($fileinfo) {
            if (!$fileinfo->isFile()) {
                return false;
            }
            return $fileinfo->getPathname();
        };
        foreach ($iterator as $fileinfo) {
            $filedata = $filter($fileinfo);
            if (!$filedata) {
                continue;
            }
            $result[] = $filedata;
        }

        return $result;
    }
}
