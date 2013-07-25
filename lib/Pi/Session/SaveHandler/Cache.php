<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Session\SaveHandler;

use Zend\Session\SaveHandler\Cache as ZendCache;

/**
 * Cache session save handler
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Cache extends ZendCache
{
    /**
     * Constructor
     *
     * @param string|array $storage Storage class or class with options
     */
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
