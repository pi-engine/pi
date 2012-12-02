<?php
/**
 * DB Table session save handler
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
 * @package         Pi\Session
 * @version         $Id$
 */

namespace Pi\Session\SaveHandler;
use Zend\Session\SaveHandler\Cache as ZendCache;

class Cache extends ZendCache
{
    public function __construct($storage)
    {
        if (is_string($storage)) {
            $storageClass   = $storage;
            $options        = array();
        } else {
            $storageClass   = $storage['class'];
            $options        = $storage['options'];
        }
        $storageAdapter = new $storageClass($options);
        parent::__construct($storageAdapter);
    }
}
