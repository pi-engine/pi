<?php
/**
 * Cache plugin manager
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
 * @since           3.0
 * @package         Pi\Cache
 * @version         $Id$
 */

namespace Pi\Cache\Storage;

use Zend\Cache\Storage\AdapterPluginManager as ZendAdapterPluginManager;

class AdapterPluginManager extends ZendAdapterPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'filesystem'     => 'Pi\Cache\Storage\Adapter\Filesystem',

        'apc'            => 'Zend\Cache\Storage\Adapter\Apc',
        'memcached'      => 'Zend\Cache\Storage\Adapter\Memcached',
        'memory'         => 'Zend\Cache\Storage\Adapter\Memory',
        'dba'            => 'Zend\Cache\Storage\Adapter\Dba',
        'wincache'       => 'Zend\Cache\Storage\Adapter\WinCache',
        'zendserverdisk' => 'Zend\Cache\Storage\Adapter\ZendServerDisk',
        'zendservershm'  => 'Zend\Cache\Storage\Adapter\ZendServerShm',
    );
}