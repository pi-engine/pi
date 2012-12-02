<?php
/**
 * CAPTCHA service
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
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Captcha\AdapterInterface;

class Captcha extends AbstractService
{
    protected $fileIdentifier = 'captcha';

    /**
     * Load CAPTCHA adapter
     *
     * @return AdapterInterface
     */
    public function load($type = null, $options = array())
    {
        $type = $type ?: 'image';
        $class = 'Pi\\Captcha\\' . ucfirst($type);
        if (!class_exists($class)) {
            $class = 'Pi\\Captcha\\' . ucfirst($type);
        }
        if ($options) {
            $options = array_merge($this->options, $options);
        } else {
            $options = $this->options;
        }
        $captcha = new $class($options);

        return $captcha;
    }
}
