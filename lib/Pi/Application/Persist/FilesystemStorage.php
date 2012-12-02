<?php
/**
 * Kernel persist
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
 * @subpackage      Persist
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Persist;
use Pi;

class FilesystemStorage extends AbstractStorage
{
    protected $cacheDir;

    public function __construct($options = array())
    {
        $this->cacheDir = isset($options['cache_dir']) ? $options['cache_dir'] : Pi::path('cache');
    }

    public function getType()
    {
        return 'file';
    }

    protected function fileName($id, $hash = false)
    {
        return sprintf('%s/%s.php', $this->cacheDir, $this->prefix(($hash ? md5($id) : $id)));
    }

    /**
     * Test if an item is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Item id
     * @return mixed|false Cached datas
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
     * Save some data in a key
     *
     * @param  mixed $data      Data to put in cache
     * @param  string $id       Store id
     * @return boolean True if no problem
     */
    public function save($data, $id, $ttl = 0)
    {
        $cacheFile = $this->fileName($id);
        if (!$file = fopen($cacheFile, "w")) {
            throw new \Exception(sprintf('Cache file "%s" can not be created.', $cacheFile));
        }
        $content = "<?php return " . var_export($data, true) . ";?>";
        fwrite($file, $content);
        fclose($file);
        return true;
    }

    /**
     * Remove an item
     *
     * @param  string $id Data id to remove
     * @return boolean True if ok
     */
    public function remove($id)
    {
        $cacheFile = $this->fileName($id);
        return unlink($cacheFile);
    }

    /**
     * Clear cached entries
     *
     * @param string $prefix
     * @return boolean True if ok
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
