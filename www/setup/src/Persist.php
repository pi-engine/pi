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
    /** @var string Identifier for container */
    const PERSIST_IDENTIFIER = 'PI-SETUP.tmp';

    /** @var string Storage for persistent data */
    protected $storage = 'session'; //or 'file';

    /** @var string Directory to store tem file */
    protected $tmpDir;

    /** @var array Container for persistent data */
    protected $container = array();

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
     * @param bool       $flag
     *
     * @throws \Exception
     * @return mixed
     */
    protected function storage($method, $data = null, $flag = true)
    {
        $result = null;
        $_this = $this;
        $fileLookup = function() use ($_this) {
            return $this->getTmpDir() . '/' . static::PERSIST_IDENTIFIER;
        };

        switch ($this->storage) {
            case 'file':
                switch ($method) {
                    case 'load':
                        $file = $fileLookup();
                        if (file_exists($file)) {
                            $content    = file_get_contents($file);
                            $result     = json_decode($content, true);
                        } else {
                            $result = array();
                        }
                        break;
                    case 'save':
                        $file = $fileLookup();
                        file_put_contents($file, json_encode($data));
                        break;
                    case 'destroy':
                        $file = $fileLookup();
                        if (file_exists($file)) {
                            $status = @unlink($file);
                            if (!$status) {
                                throw new \Exception('Temp persistent data file was not destroyed: ' . $file);
                            }
                        }
                        break;
                }
                break;

            case 'session':
            default:
                switch ($method) {
                    case 'load':
                        session_start();
                        if (isset($_SESSION[static::PERSIST_IDENTIFIER])) {
                            $result = $_SESSION[static::PERSIST_IDENTIFIER];
                        } else {
                            $result = array();
                        }
                        break;
                    case 'save':
                        $_SESSION[static::PERSIST_IDENTIFIER] = $data;
                        if ($flag) {
                            session_write_close();
                        }
                        break;
                    case 'destroy':
                        if (isset($_SESSION[static::PERSIST_IDENTIFIER])) {
                            unset($_SESSION[static::PERSIST_IDENTIFIER]);
                        }
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
        $this->container = $this->storage('load');
    }

    /**
     * Save container to storage
     *
     * @param array $container
     * @param bool $flag
     */
    public function save($container = null, $flag = true)
    {
        $data = (null === $container) ? $this->container : $container;
        $this->storage('save', $data, $flag);

        return;
    }

    /**
     * Destroy persistent data
     *
     * @return bool
     */
    public function destroy()
    {
        $this->container = array();
        $this->storage('destroy');

        return true;
    }

    /**
     * Set a param
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        if (!is_array($this->container)) {
            $this->container = array();
        }
        $this->container[$key] = $value;

        return $this;
    }

    /**
     * Get a param
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->container[$key]) ? $this->container[$key] : null;
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
