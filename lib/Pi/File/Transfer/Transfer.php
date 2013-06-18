<?php
/**
 * File transfer
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
 * @package         Pi\File
 */

namespace Pi\File\Transfer;

use Pi;
use Zend\File\Transfer\Transfer as TransferHandler;
use Zend\File\Exception;

/**
 * {@inheritDoc}
 */
class Transfer extends TransferHandler
{
    /**
     * {@inheritDoc}
     */
    public function setAdapter($adapter, $direction = false, $options = array())
    {
        if (!is_string($adapter)) {
            throw new Exception\InvalidArgumentException('Adapter must be a string');
        }

        if ($adapter[0] != '\\') {
            $adapter = '\Pi\File\Transfer\Adapter\\' . ucfirst($adapter);
            if (!class_exists($adapter)) {
                $adapter = '\Zend\File\Transfer\Adapter\\' . ucfirst($adapter);
            }
        }
        parent::setAdapter($adapter, $direction, $options);

        return $this;
    }
}
