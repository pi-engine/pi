<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Persist;

use Pi;

/**
 * File system persist storage
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FilesystemStorage extends AbstractStorage
{
    /**
     * Path to cached files
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->cacheDir = isset($options['cache_dir'])
            ? $options['cache_dir'] : Pi::path('cache');
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'file';
    }

    /**
     * Find file name for a cached object
     *
     * @param string $id
     * @param bool $hash
     * @return string
     */
    protected function fileName($id, $hash = false)
    {
        return sprintf(
            '%s/%s.php',
            $this->cacheDir,
            $this->prefix(($hash ? md5($id) : $id))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $cacheFile = $this->fileName($id);
        if (file_exists($cacheFile)) {
            return include $cacheFile;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id, $ttl = 0)
    {
        $cacheFile = $this->fileName($id);
        if (!$file = fopen($cacheFile, 'w')) {
            throw new \Exception(
                sprintf('Cache file "%s" can not be created.', $cacheFile)
            );
        }
        $content = '<?php return ' . var_export($data, true) . ';?>';
        fwrite($file, $content);
        fclose($file);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $cacheFile = $this->fileName($id);

        return unlink($cacheFile);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $cacheFiles = sprintf('%s/%s.php', $this->cacheDir, $this->prefix(''));
        $list = glob($cacheFiles);
        if ($list) {
            foreach ($list as $file) {
                unlink($file);
            }
        }

        return true;
    }
}
