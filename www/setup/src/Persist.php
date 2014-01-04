<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Setup;

/**
 * Persist handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Persist
{
    const PERSIST_FILE = 'PI-PERSIST.tmp';

    /** @var string */
    protected $tmpDir;

    protected $container = array();

    public function __construct($tmpDir = null)
    {
        $this->setTmpDir($tmpDir);
    }

    public function setTmpDir($tmpDir)
    {
        $this->tmpDir = $tmpDir;

        return $this;
    }

    /**
     * Determine system TMP directory and detect if we have read access
     *
     * @throws \Exception
     * @return string
     * @see \Zend\File\Transfer\Adapter\AbstractAdapter::getTmpDir
     */
    protected function getTmpDir()
    {
        if (null === $this->tmpDir) {
            $tmpdir = array();
            if (function_exists('sys_get_temp_dir')) {
                $tmpdir[] = sys_get_temp_dir();
            }

            if (!empty($_ENV['TMP'])) {
                $tmpdir[] = realpath($_ENV['TMP']);
            }

            if (!empty($_ENV['TMPDIR'])) {
                $tmpdir[] = realpath($_ENV['TMPDIR']);
            }

            if (!empty($_ENV['TEMP'])) {
                $tmpdir[] = realpath($_ENV['TEMP']);
            }

            $upload = ini_get('upload_tmp_dir');
            if ($upload) {
                $tmpdir[] = realpath($upload);
            }

            foreach ($tmpdir as $directory) {
                //if ($this->isPathWriteable($directory)) {
                if (is_writable($directory)) {
                    $this->tmpDir = $directory;
                }
            }

            if (empty($this->tmpDir)) {
                // Attempt to detect by creating a temporary file
                $tempFile = tempnam(md5(uniqid(rand(), true)), '');
                if ($tempFile) {
                    $this->tmpDir = realpath(dirname($tempFile));
                    unlink($tempFile);
                } else {
                    throw new \Exception('Could not locate persist file.');
                }
            }

            $this->tmpDir = rtrim($this->tmpDir, "/\\");
        }

        return $this->tmpDir;
    }

    protected function storage()
    {
        $result = '';
        $tmpDir = $this->getTmpDir();
        if ($tmpDir) {
            $result = $tmpDir . '/' . static::PERSIST_FILE;
        }

        return $result;
    }

    public function load()
    {
        $file = $this->storage();
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $this->container = json_decode($content, true);
        } else {
            $this->container = array();
        }
    }

    public function save($container = null)
    {
        $data = (null === $container) ? $this->container : $container;
        $file = $this->storage();
        if ($file) {
            file_put_contents($file, json_encode($data));
        }

        return;
    }

    public function destroy()
    {
        $file = $this->storage();
        if ($file) {
            @unlink($file);
        }
        $this->container = array();

        return true;
    }

    public function set($key, $value)
    {
        $this->container[$key] = $value;

        return $this;
    }

    public function get($key)
    {
        return isset($this->container[$key]) ? $this->container[$key] : null;
    }
}
