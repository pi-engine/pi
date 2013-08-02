<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
