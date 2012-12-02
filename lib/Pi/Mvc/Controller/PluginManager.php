<?php
/**
 * Controller plugin manager class
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
namespace Pi\Mvc\Controller;

use Zend\Mvc\Controller\PluginManager as ZendPluginManager;

class PluginManager extends ZendPluginManager
{
    /**
     * Default set of plugins
     *
     * @var array
     */
    protected $invokableClasses = array(
        'acl'               => 'Pi\Mvc\Controller\Plugin\Acl',
        'cache'             => 'Pi\Mvc\Controller\Plugin\Cache',
        //'params'            => 'Pi\Mvc\Controller\Plugin\Params',
        'redirect'          => 'Pi\Mvc\Controller\Plugin\Redirect',
        'url'               => 'Pi\Mvc\Controller\Plugin\Url',

        //'flashmessenger'    => 'Zend\Mvc\Controller\Plugin\FlashMessenger',
        //'forward'           => 'Zend\Mvc\Controller\Plugin\Forward',
        //'layout'            => 'Zend\Mvc\Controller\Plugin\Layout',
    );

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
            $invokableClass = sprintf('%s\\Plugin\\%s', __NAMESPACE__, ucfirst($name));
            if (!class_exists($invokableClass)) {
                $invokableClass = sprintf('Zend\\Mvc\\Controller\\Plugin\\%s', ucfirst($name));
            }
            $name = $invokableClass;
        }
        return parent::get($name, $options, $usePeeringServiceManagers);
    }
}
