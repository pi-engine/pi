<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Controller;

use Laminas\Mvc\Controller\PluginManager as ZendPluginManager;

/**
 * Plugin load manager
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PluginManager extends ZendPluginManager
{
    /**
     * {@inheritDoc}
     * Default set of plugins
     * @var array
     */
    protected $invokableClasses
        = [
            'acceptableviewmodelselector' => 'Laminas\Mvc\Controller\Plugin\AcceptableViewModelSelector',
            'filepostredirectget'         => 'Laminas\Mvc\Controller\Plugin\FilePostRedirectGet',
            'flashmessenger'              => 'Laminas\Mvc\Controller\Plugin\FlashMessenger',
            'layout'                      => 'Laminas\Mvc\Controller\Plugin\Layout',
            'params'                      => 'Laminas\Mvc\Controller\Plugin\Params',
            'postredirectget'             => 'Laminas\Mvc\Controller\Plugin\PostRedirectGet',
            'redirect'                    => 'Laminas\Mvc\Controller\Plugin\Redirect',
            'url'                         => 'Laminas\Mvc\Controller\Plugin\Url',

            // Pi custom plugins
            'flashmessenger'              => 'Pi\Mvc\Controller\Plugin\FlashMessenger',
            'params'                      => 'Pi\Mvc\Controller\Plugin\Params',
            'redirect'                    => 'Pi\Mvc\Controller\Plugin\Redirect',
            'url'                         => 'Pi\Mvc\Controller\Plugin\Url',
            'cache'                       => 'Pi\Mvc\Controller\Plugin\Cache',
            'config'                      => 'Pi\Mvc\Controller\Plugin\Config',
            'jump'                        => 'Pi\Mvc\Controller\Plugin\Jump',
            'view'                        => 'Pi\Mvc\Controller\Plugin\View',
        ];

    /**
     * {@inheritDoc}
     * Canonicalize name
     *
     * @param  string $name
     * @return string
     */
    protected function canonicalizeName($name)
    {
        static $inCanonicalization = false;

        $name = strtolower($name);

        if ($inCanonicalization) {
            $inCanonicalization = false;
            return $name;
        }

        $invokableClass = null;
        if (!isset($this->invokableClasses[$name])
            && false === strpos($name, '\\')
        ) {
            $invokableClass = sprintf(
                '%s\Plugin\\%s',
                __NAMESPACE__,
                ucfirst($name)
            );
            if (!class_exists($invokableClass)) {
                $invokableClass = sprintf(
                    'Laminas\Mvc\Controller\Plugin\\%s',
                    ucfirst($name)
                );
            }
            $name = $invokableClass;
        }

        $cName = parent::canonicalizeName($name);

        if ($invokableClass && class_exists($invokableClass)) {
            $inCanonicalization = true;
            $this->setInvokableClass($cName, $invokableClass);
            $inCanonicalization = false;
        }

        return $cName;
    }
}
