<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Authentication\Storage;

use Pi;
use Zend\Authentication\Storage\StorageInterface as ZendStorageInterface;

/**
 * Pi authentication storage interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
interface StorageInterface extends ZendStorageInterface
{
    /**
     * Set options
     *
     * @param array $options
     * @return void
     */
    public function setOptions($options = array());
}
