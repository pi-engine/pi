<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
     * @params string|null $name
     * @return array Config or array of all configs
     */
    public function __invoke($name = null)
    {
        return $this->getConfig($name);
    }

    /**
     * Get config data
     *
     * @param string|null $name
     * @return array|mixed
     */
    public function getConfig($name = null)
    {
        if (null === $this->configs) {
            $this->configs = Pi::service('module')->config(
                '',
                $this->getController()->getModule()
            );
        }

        return $name ? $this->configs[$name] : $this->configs;
    }
}
