<?php
/**
 * Standard application engine class
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
 * @package         Application
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Engine;

use Pi;
use Pi\Mvc\Application\Setup as Application;
use Pi\Mvc\Bootstrap\Setup as Bootstrap;

/**
 * Pi standard application engine
 *
 * Tasks: load configs, default listeners, module manager, bootstrap, application; boot application; run application
 *
 * @author      Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

class Setup extends AbstractEngine
{
    /**
     * Options for application
     * @var array
     */
    protected $options = array(
        'bootstrap' => array(
            'resource'  => array(
                'database'  => 'db',
                'intl'      => array(
                    'translator'    => array(
                    )
                ),
            ),
        )
    );

    /**
     * Run the application
     */
    public function run()
    {
        $this->bootstrap();
        $this->application()->run()->send();
    }

    /**
     * Bootstrap
     */
    public function bootstrap()
    {
        $this->loadService();
        $this->application();
        $this->setupBootstrap()->bootstrap($this->application);
        return $this;
    }

    /**
     * Load application
     */
    public function application()
    {
        if (!isset($this->application)) {
            $this->application = new Application($this->section);
            Pi::registry('application', $this->application);
        }

        return $this->application;
    }

    /**
     * Load bootstrap
     *
     * @return Bootstrap
     */
    protected function setupBootstrap()
    {
        // Prepare config
        $config = isset($this->options['bootstrap']['config']) ? $this->options['bootstrap']['config'] : array();
        // Instantiate Bootstrap
        $bootstrap = new Bootstrap($config);
        $bootstrap->setApplication($this->application);
        $bootstrap->setupResource($this->options['bootstrap']['resource']);

        return $bootstrap;
    }
}
