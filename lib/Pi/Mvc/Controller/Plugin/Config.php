<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Controller\Plugin;

use Pi;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Config access plugin for controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config extends AbstractPlugin
{
    /** @var array Loaded configs */
    protected $configs;

    /**
     * Invoke as a functor
     *
     * @param string $name
     *
     * @return array Config or array of all configs
     */
    public function __invoke($name = '')
    {
        return $this->getConfig($name);
    }

    /**
     * Get config data
     *
     * @param string $name
     * @return array|mixed
     */
    public function getConfig($name = '')
    {
        if ($name) {
            $config = Pi::service('config')->module($name);
        } else {
            $config = Pi::service('config')->module();
        }

        return $config;
    }
}
