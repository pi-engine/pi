<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Engine;

use Pi;
use Pi\Mvc\Application;

/**
 * Abstract application engine class for invoking applications
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractEngine
{
    /** @var string Front section */
    const FRONT     = 'front';

    /** @var string Admin section */
    const ADMIN     = 'admin';

    /** @var string Feed section */
    const FEED      = 'feed';

    /** @var string API section */
    const API       = 'api';

    /** @var string Cront section */
    const CRON      = 'cron';

    /** @var string Widget section */
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
     *
     * @return bool
     */
    abstract public function run();

    /**
     * Load application
     *
     * @return Application
     */
    abstract public function application();

    /**
     * Load options from data file
     *
     *  - Bootstrap config and listener options
     *  - Global config
     *  - Services
     *
     * @param array $options
     * @return $this
     */
    public function setOption(array $options)
    {
        if ($this->fileIdentifier) {
            // Load option data from var folder
            $opt = Pi::config()->load(
                sprintf('application.%s.php', $this->fileIdentifier)
            );
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
        $this->options = $this->options
                         ? array_merge_recursive($this->options, $options)
                         : $options;

        return $this;
    }

    /**
     * Get section
     *
     * @return string
     */
    public function section()
    {
        return static::SECTION;
    }
}
