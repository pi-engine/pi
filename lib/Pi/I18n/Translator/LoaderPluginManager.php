<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
 * {@inheritDoc}
 * @see Zend\I18n\Translator\LoaderPluginManager
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoaderPluginManager extends ZendLoaderPluginManager
{
    /**
     * Default set of loaders
     * @var array
     */
    protected $invokableClasses = array();

    /**
     * Default set of filters
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
    public function get(
        $name,
        $options = array(),
        $usePeeringServiceManagers = true
    ) {
        // Canonize invokable class from name
        if (!$this->has($name) && !class_exists($name)) {
            // Lookup in default invokable list
            $cname = strtolower(
                str_replace(array('-', '_', ' ', '\\', '/'), '', $name)
            );
            if (isset($this->invokableList[$cname])) {
                $invokableClass = 'Pi\\' . $this->invokableList[$cname];
                if (!class_exists($invokableClass)) {
                    $invokableClass = 'Zend\\' . $this->invokableList[$cname];
                }
                $name = $invokableClass;
            // Lookup in helper locations
            } else {
                $class = str_replace(' ', '', ucwords(
                    str_replace(array('-', '_', '\\', '/'), ' ', $name)
                ));
                if (class_exists('Pi\I18n\Translator\Loader\\' . $class)) {
                    $name = 'Pi\I18n\Translator\Loader\\' . $class;
                } else {
                    $name = 'Zend\I18n\Translator\Loader\\' . $class;
                }
            }
        }
        $filter = parent::get($name, $options, $usePeeringServiceManagers);

        return $filter;
    }
}
