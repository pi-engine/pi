<?php
/**
 * Bootstrap resource interface
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi\Application\Engine\AbstractEngine;

/**
 * Abstract class for bootstrap resources
 */
abstract class AbstractResource
{
    /**
     * Bootstrap options
     * @var array
     */
    protected $options = array();

    /**
     * Pi Engine handler
     * @var AbstractEngine
     */
    protected $engine;

    /**
     * Pi Application handler
     * @var \Pi\Mvc\Application
     */
    protected $application;

    /**
     * Constructor
     *
     * @param AbstractEngine    $engine
     * @param array             $options
     */
    public function __construct(AbstractEngine $engine, $options = array())
    {
        $this->options = $options;
        $this->engine = $engine;
        $this->application = $engine->application();
    }

    /**
     * Boot the resource
     */
    abstract public function boot();
}
