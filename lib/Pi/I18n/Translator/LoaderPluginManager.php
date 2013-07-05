<?php
/**
 * Plugin manager implementation for translation loaders.
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
 * @package         Pi\I18n
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\I18n\Translator;

use Zend\I18n\Translator\LoaderPluginManager as ZendLoaderPluginManager;

/**
 * Plugin manager implementation for translation loaders.
 *
 * Enforces that filters retrieved are either callbacks or instances of
 * Loader\LoaderInterface. Additionally, it registers a number of default
 * loaders.
 *
 * @see Zend\I18n\Translator\LoaderPluginManager
 */
class LoaderPluginManager extends ZendLoaderPluginManager
{
    /**
     * Default set of loaders
     *
     * @var array
     */
    protected $invokableClasses = array();

    /**
     * Default set of filters
     *
     * @var array
     */
    protected $invokableList = array(
        'phparray'  => 'I18n\Translator\Loader\PhpArray',
        'gettext'   => 'I18n\Translator\Loader\Gettext',
        'csv'       => 'I18n\Translator\Loader\Csv',
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
        // Canonize invokable class from name
        if (!$this->has($name) && !class_exists($name)) {
            // Lookup in default invokable list
            $cname = strtolower(str_replace(array('-', '_', ' ', '\\', '/'), '', $name));
            if (isset($this->invokableList[$cname])) {
                $invokableClass = 'Pi\\' . $this->invokableList[$cname];
                if (!class_exists($invokableClass)) {
                    $invokableClass = 'Zend\\' . $this->invokableList[$cname];
                }
                $name = $invokableClass;
            // Lookup in helper locations
            } else {
                $class = str_replace(' ', '', ucwords(str_replace(array('-', '_', '\\', '/'), ' ', $name)));
                if (class_exists('Pi\\I18n\\Translator\\Loader\\' . $class)) {
                    $name = 'Pi\\I18n\\Translator\\Loader\\' . $class;
                } else {
                    $name = 'Zend\\I18n\\Translator\\Loader\\' . $class;
                }
            }
        }
        $filter = parent::get($name, $options, $usePeeringServiceManagers);
        return $filter;
    }
}
