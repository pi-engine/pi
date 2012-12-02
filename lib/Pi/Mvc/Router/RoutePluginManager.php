<?php
/**
 * Route plugin manager
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
 * @subpackage      Router
 * @version         $Id$
 */

namespace Pi\Mvc\Router;

use Zend\Mvc\Router\RoutePluginManager as ZendRoutePluginManager;

class RoutePluginManager extends ZendRoutePluginManager
{
    protected $subNamespace = 'Http';

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array $options
     * @param  bool $usePeeringServiceManagers
     * @return object
     */
    public function get($name, $options = array(), $usePeeringServiceManagers = true)
    {
        if (!$this->has($name) && !class_exists($name)) {
            $class = sprintf('%s\\%s\\%s', __NAMESPACE__, $this->subNamespace, ucfirst($name));
            if (!class_exists($class)) {
                $class = sprintf('Zend\\Mvc\\Router\\%s\\%s', $this->subNamespace, ucfirst($name));
            }
            $name = $class;
        }

        return parent::get($name, $options, $usePeeringServiceManagers);
    }

    public function setSubNamespace($namespace)
    {
        $this->subNamespace = $namespace;
        return $this;
    }
}
