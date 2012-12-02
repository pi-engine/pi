<?php
/**
 * Application engine abstraction
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
use Pi\Mvc\Application;

/**
 * Abstract application engine class for invoking applications
 */
abstract class AbstractEngine
{
    const FRONT     = 'front';
    const ADMIN     = 'admin';
    const API       = 'api';
    const CRON      = 'cron';
    const FEED      = 'feed';
    const WIDGET    = 'widget';

    /**
     * Section name
     * @var string
     */
    const SECTION = FRONT;

    /**
     * Identifier for file name of option data
     * @var string
     */
    protected $fileIdentifier = '';

    /**
     * Options for application
     * @var array
     */
    protected $options = array();

    /**
     * Application handler
     * @var Application
     */
    protected $application;

    /**
     * Bootstrap
     */
    //protected $bootstrap;

    /**
     * Constructor
     *
     * @param  array $options
     * @return void
     */
    public function __construct($options = array())
    {
        $this->setOption($options);
    }

    /**
     * Run the application
     */
    abstract public function run();

    /**
     * Bootstrap
     */
    //abstract public function bootstrap();

    /**
     * Load application
     */
    abstract public function application();

    /**
     * Load options from data file
     *  - Bootstrap config and listener options
     *  - Global config
     *  - Services
     *
     * @param array $options
     * @return AbstractEngine
     */
    public function setOption(array $options)
    {
        if ($this->fileIdentifier) {
            // Load option data from var folder
            $opt = Pi::config()->load(sprintf('application.%s.php', $this->fileIdentifier));
            // Set configs if available
            if (!empty($options['config'])) {
                Pi::config()->setConfigs($options['config']);
                unset($options['config']);
            }
            $options = $options ? array_merge_recursive($opt, $options) : $opt;
        }
        if (!empty($options['config'])) {
            Pi::config()->setConfigs($options['config']);
            unset($options['config']);
        }
        $this->options = $this->options ? array_merge_recursive($this->options, $options) : $options;

        return $this;
    }

    public function section()
    {
        return static::SECTION;
    }
}
