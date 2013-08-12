<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Cache maintenance
 *
 * Feature list:
 *
 *  1. List of cache types to maintain
 *  2. Flush a type of cache
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CacheController extends ActionController
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
            'stat'          => __('File status cache'),
            'apc'           => __('APC file cache'),
            'folder'        => __('System cache file folder'),
            'persist'       => __('System persistent data'),
            'application'   => __('Application cache'),
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
            __('Application cache [%s]'),
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
            $page['title'] = sprintf(__('Page cache [%s]'), $cacheStorageName);
            $modules = Pi::service('module')->meta();
            $page['modules'] = array_keys($modules);
            $this->view()->assign('page', $page);
        }

        $registryList = Pi::service('registry')->getList();
        sort($registryList);

        $this->view()->assign('type', $type);
        $this->view()->assign('list', $cacheList);
        $this->view()->assign('registry', $registryList);
        $this->view()->assign('title', __('Cache list'));
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

        switch (strtolower($type)) {
            case 'stat':
                clearstatcache(true);
                break;
            case 'folder':
                $this->flushFolder();
                break;
            case 'apc':
                $this->flushApc();
                break;
            case 'persist':
                Pi::persist()->flush();
                break;
            case 'application':
                $this->flushApplication();
                break;
            case 'page':
                $this->flushPage($item);
                break;
            case 'registry':
                if (!empty($item)) {
                    Pi::registry($item)->flush();
                } else {
                    Pi::service('registry')->flush();
                }
                break;
            case 'all':
                clearstatcache(true);
                $this->flushApc();
                $this->flushFolder();
                Pi::persist()->flush();
                Pi::service('registry')->flush();
                $this->flushApplication();
                $this->flushPage();
            default:
                break;
        }

        return array(
            'status'    => 1,
            'message'   => __('Cache is flushed successfully.'),
        );
    }

    /**
     * Flush APC caches
     *
     * @return void
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
     */
    protected function flushPage($namespace = null)
    {
        Pi::service('render')->flushCache($namespace ?: null);

        return;
    }
}
