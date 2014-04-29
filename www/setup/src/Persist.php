<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Setup;

/**
 * Persist handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Persist
{
    /** @var string Identifier for container */
    const PERSIST_IDENTIFIER = 'PI-SETUP-TMP';

    /** @var string Storage for persistent data */
    protected $storage = 'session'; //or 'file';

    /** @var string Directory to store tem file */
    protected $tmpDir;

    /** @var array Container for persistent data */
    protected static $container;

    /**
     * Constructor
     *
     * @param string $storage
     * @param string $dir
     */
    public function __construct($storage = '', $dir = '')
    {
        $this->storage  = $storage ?: 'session';
        if ($dir) {
            $this->setTmpDir($dir);
        }
    }

    /**
     * Storage methods
     *
     * @param string     $method
     * @param array|null $data
     *
     * @throws \Exception
     * @return mixed
     */
    protected function storage($method, $data = null)
    {
        $result = null;
        $fileLookup = function() {
            return $this->getTmpDir() . '/' . static::PERSIST_IDENTIFIER;
        };

        switch ($this->storage) {
            case 'file':
                switch ($method) {
                    case 'load':
                        $file = $fileLookup();
                        if (file_exists($file)) {
                            $content = file_get_contents($file);
                            if ($content) {
                                $result = (array) json_decode($content, true);
                            }
                        } else {
                            $result = array();
                        }
                        break;
                    case 'set':
                        break;
                    case 'save':
                        $file = $fileLookup();
                        file_put_contents($file, json_encode($data));
                        break;
                    case 'destroy':
                        $file = $fileLookup();
                        if (file_exists($file)) {
                            file_put_contents($file, '');
                        }
                        break;
                }
                break;

            case 'session':
            default:
                switch ($method) {
                    case 'load':
                        session_start();
                        $result = empty($_SESSION) ? array() : (array) $_SESSION;
                        session_write_close();
                        break;
                    case 'save':
                        session_start();
                        $_SESSION = $data;
                        session_write_close();
                        break;
                    case 'set':
                        if (isset($_SESSION)) {
                            $_SESSION = $data;
                        }
                        break;
                    case 'destroy':
                        session_start();
                        $_SESSION = null;
                        session_regenerate_id(true);
                        session_write_close();
                        break;
                }
                break;
        }

        return $result;
    }

    /**
     * Loads container from storage
     */
    public function load()
    {
        static::$container = $this->storage('load');
    }

    /**
     * Save container to storage
     */
    public function save()
    {
        if (null === static::$container) {
            throw new \Exception('Persist not initialized');
        }
        $this->storage('save', static::$container);

        return;
    }

    /**
     * Destroy persistent data
     *
     * @return bool
     */
    public function destroy()
    {
        static::$container = null;
        $this->storage('destroy');

        return true;
    }

    /**
     * Set a param
     *
     * @param string|array $key
     * @param mixed  $value
     *
     * @throws \Exception
     * @return $this
     */
    public function set($key, $value = null)
    {
        if (null === static::$container) {
            throw new \Exception('Persist not initialized');
        }
        if (!$key) {
            throw new \Exception('Missing parameter name');
        }
        if (is_array($key)) {
            static::$container = $key;
        } else {
            static::$container[$key] = $value;
        }
        $this->storage('set', static::$container);

        return $this;
    }

    /**
     * Get a param
     *
     * @param string $key
     *
     * @throws \Exception
     * @return mixed
     */
    public function get($key = '')
    {
        if (null === static::$container) {
            throw new \Exception('Persist not initialized');
        }
        if (!$key) {
            $result = static::$container;
        } else {
            $result = isset(static::$container[$key]) ? static::$container[$key] : null;
        }

        return $result;
    }

    /**
     * Set temp dir for file storage
     *
     * @param string $tmpDir
     *
     * @throws \Exception
     * @return $this
     */
    public function setTmpDir($tmpDir)
    {
        if (!is_writable($tmpDir)) {
            throw new \Exception('`tmp` directory is not writable.');
        }
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
    public function getTmpDir()
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

}