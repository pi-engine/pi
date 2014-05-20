<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Cache flush maintenance
 *
 * Feature list:
 *
 *  1. List of cache types to maintain
 *  2. Flush a type of cache
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FlushController extends ActionController
{
    /**
     * List of caches
     *
     * @return void
     */
    public function indexAction()
    {
        $type = $this->params('type');

        $cacheList = array(
            'stat'          => _a('File status cache'),
            'apc'           => _a('APC file cache'),
            'file'          => _a('System cache files'),
            'persist'       => _a('System persistent data'),
            'module'        => _a('Module cache'),
            'comment'       => _a('Comment cache'),
        );
        if (!function_exists('apc_clear_cache')) {
            unset($cacheList['apc']);
        } elseif (class_exists('\\APCIterator')) {
            $apcIterator = new \APCIterator('file');
            $size = $apcIterator->getTotalSize();
            foreach (array('','K','M','G') as $i => $k) {
                if ($size < 1024) break;
                $size /= 1024;
            }
            $totalSize = sprintf("%5.1f %s", $size, $k);
            $totalCount = $apcIterator->getTotalCount();
            $cacheList['apc'] .= ' (' . $totalCount . '-' . $totalSize . ')';
        }
        $cacheStorageClass = get_class(Pi::service('cache')->storage());
        $cacheStorageName = substr(
            $cacheStorageClass,
            strrpos($cacheStorageClass, '\\') + 1
        );
        $cacheList['application'] = sprintf(
            _a('Application cache [%s]'),
            $cacheStorageName
        );

        $frontConfig = Pi::config()->load('application.front.php');
        if (!empty($frontConfig['resource']['cache'])) {
            if (!empty($frontConfig['resource']['cache']['storage'])) {
                $cacheStorage = Pi::service('cache')->loadStorage(
                    $frontConfig['resource']['cache']['storage']
                );
            } else {
                $cacheStorage = Pi::service('cache')->storage();
            }
            $cacheStorageClass = get_class($cacheStorage);
            $cacheStorageName = substr(
                $cacheStorageClass,
                strrpos($cacheStorageClass, '\\') + 1
            );
            $page['title'] = sprintf(_a('Page cache [%s]'), $cacheStorageName);
            $modules = Pi::service('module')->meta();
            $page['modules'] = array_keys($modules);
            $this->view()->assign('page', $page);
        }

        $registryList = Pi::service('registry')->getList();
        sort($registryList);

        $this->view()->assign('type', $type);
        $this->view()->assign('list', $cacheList);
        $this->view()->assign('registry', $registryList);
        $this->view()->assign('title', _a('Cache list'));
        //$this->view()->setTemplate('cache-list');
    }

    /**
     * Flush a type of cache
     *
     * @return array Result pair of status and message
     */
    public function flushAction()
    {
        $type = $this->params('type');
        $item = $this->params('item');

        try {
            Pi::service('cache')->flush($type, $item);
            $status = 1;
            $message = _a('Cache is flushed successfully.');
        } catch (\Exception $e) {
            $status = 0;
            $message = sprintf(_a('Cache flush failed: %s'), $e->getMessage());
        }

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * Flush APC caches
     *
     * @return void
     * @deprecated
     */
    protected function flushApc()
    {
        if (!function_exists('apc_clear_cache')) {
            return;
        }
        apc_clear_cache();

        return;
    }

    /**
     * Flush filesystem folders
     *
     * @return void
     * @deprecated
     */
    protected function flushFolder()
    {
        $path = Pi::path('cache');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $object) {
            $filename = $object->getFilename();
            if ($object->isFile() && 'index.html' !== $filename) {
                unlink($object->getPathname());
            } elseif ($object->isDir() && '.' != $filename[0]) {
                rmdir($object->getPathname());
            }
        }

        return;
    }

    /**
     * Flush applications (modules)
     *
     * @return void
     * @deprecated
     */
    protected function flushApplication()
    {
        Pi::service('cache')->clearByNamespace();
        $modules = Pi::service('module')->meta();
        foreach (array_keys($modules) as $module) {
            Pi::service('cache')->clearByNamespace($module);
        }

        return;
    }

    /**
     * Flush page caches
     *
     * @param string|null $namespace
     * @return void
     * @deprecated
     */
    protected function flushPage($namespace = null)
    {
        Pi::service('render_cache')->flushCache($namespace ?: null);

        return;
    }

    /**
     * Flush comment caches
     *
     * @return void
     * @deprecated
     */
    protected function flushComment()
    {
        Pi::service('cache')->clearByNamespace('comment');

        return;
    }
}
