<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller;

use Zend\Mvc\Controller\PluginManager as ZendPluginManager;

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
    protected $____invokableClasses = array(
        'acceptableviewmodelselector' =>
            'Zend\Mvc\Controller\Plugin\AcceptableViewModelSelector',
        'filepostredirectget'         =>
            'Zend\Mvc\Controller\Plugin\FilePostRedirectGet',
        'flashmessenger'              =>
            'Zend\Mvc\Controller\Plugin\FlashMessenger',
        'forward'                     => 'Zend\Mvc\Controller\Plugin\Forward',
        'layout'                      => 'Zend\Mvc\Controller\Plugin\Layout',
        'params'                      => 'Zend\Mvc\Controller\Plugin\Params',
        'postredirectget'             =>
            'Zend\Mvc\Controller\Plugin\PostRedirectGet',
        'redirect'                    => 'Zend\Mvc\Controller\Plugin\Redirect',
        'url'                         => 'Zend\Mvc\Controller\Plugin\Url',

        // Pi custom plugins
        'acl'               => 'Pi\Mvc\Controller\Plugin\Acl',
        'cache'             => 'Pi\Mvc\Controller\Plugin\Cache',
        'config'            => 'Pi\Mvc\Controller\Plugin\Config',
        'jump'              => 'Pi\Mvc\Controller\Plugin\Jump',
        'redirect'          => 'Pi\Mvc\Controller\Plugin\Redirect',
        'url'               => 'Pi\Mvc\Controller\Plugin\Url',
        'view'              => 'Pi\Mvc\Controller\Plugin\View',
    );

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

        if ($inCanonicalization) {
            $inCanonicalization = false;
            return $name;
        }

        $invokableClass = null;
        if (false === strpos($name, '\\')) {
            $invokableClass = sprintf(
                '%s\Plugin\\%s',
                __NAMESPACE__,
                ucfirst($name)
            );
            if (!class_exists($invokableClass)) {
                $invokableClass = sprintf(
                    'Zend\Mvc\Controller\Plugin\\%s',
                    ucfirst($name)
                );
            }
            $name = $invokableClass;
        }

        $cName = parent::canonicalizeName($name);

        if ($invokableClass
            && !isset($this->invokableClasses[$cName])
            && class_exists($invokableClass)
        ) {
            $inCanonicalization = true;
            $this->setInvokableClass($cName, $invokableClass);
            $inCanonicalization = false;
        }

        return $cName;
    }
}
