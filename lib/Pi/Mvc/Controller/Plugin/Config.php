<?php
/**
 * Controller plugin config class
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
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Pi;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Config extends AbstractPlugin
{
    /**
     * @var config
     */
    protected $configs;

    /**
     * Invoke as a functor
     *
     * @params string $name
     * @return config or array of all configs
     */
    public function __invoke($name = null)
    {
        return $this->getConfig($name);
    }

    public function getConfig($name = null)
    {
        if (null === $this->configs) {
            $this->configs = Pi::service('module')->config('', $this->getController()->getModule());
        }
        return $name ? $this->configs[$name] : $this->configs;
    }
}
