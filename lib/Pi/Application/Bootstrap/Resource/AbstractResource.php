<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi\Application\Engine\AbstractEngine;

/**
 * Abstract class for bootstrap resources
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
     * @var Pi\Mvc\Application
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
