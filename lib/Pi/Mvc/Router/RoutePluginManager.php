<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router;

use Laminas\Mvc\Router\RoutePluginManager as LaminasRoutePluginManager;

/**
 * {@inheritDoc}
 * Route plugin manager
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoutePluginManager extends LaminasRoutePluginManager
{
    /** @var string Namespace for routes */
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
    public function get(
        $name,
        $options = [],
        $usePeeringServiceManagers = true
    )
    {
        if (!$this->has($name) && !class_exists($name)) {
            $class = sprintf(
                '%s\\%s\\%s',
                __NAMESPACE__,
                $this->subNamespace,
                ucfirst($name)
            );
            if (!class_exists($class)) {
                $class = sprintf(
                    'Laminas\Mvc\Router\\%s\\%s',
                    $this->subNamespace,
                    ucfirst($name)
                );
            }
            $name = $class;
        }

        return parent::get($name, $options, $usePeeringServiceManagers);
    }

    /**
     * Set subnamespace
     *
     * @param string $namespace
     * @return self
     */
    public function setSubNamespace($namespace)
    {
        $this->subNamespace = $namespace;

        return $this;
    }
}
