<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Cache\Storage;

use Laminas\Cache\Storage\AdapterPluginManager as ZendAdapterPluginManager;

/**
 * Cache adapter plugin manager class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AdapterPluginManager extends ZendAdapterPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses
        = [
            'filesystem' => 'Pi\Cache\Storage\Adapter\Filesystem',
            'memcached'  => 'Pi\Cache\Storage\Adapter\Memcached',
            'redis'      => 'Pi\Cache\Storage\Adapter\Redis',

            'apc'            => 'Laminas\Cache\Storage\Adapter\Apc',
            //'memcached'      => 'Laminas\Cache\Storage\Adapter\Memcached',
            'memory'         => 'Laminas\Cache\Storage\Adapter\Memory',
            //'redis'          => 'Laminas\Cache\Storage\Adapter\Redis',
            'dba'            => 'Laminas\Cache\Storage\Adapter\Dba',
            'wincache'       => 'Laminas\Cache\Storage\Adapter\WinCache',
            'zendserverdisk' => 'Laminas\Cache\Storage\Adapter\ZendServerDisk',
            'zendservershm'  => 'Laminas\Cache\Storage\Adapter\ZendServerShm',
        ];
}
