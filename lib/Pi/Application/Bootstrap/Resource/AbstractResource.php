<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
    protected $options = [];

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
     * @param AbstractEngine $engine
     * @param array $options
     */
    public function __construct(AbstractEngine $engine, $options = [])
    {
        $this->options     = $options;
        $this->engine      = $engine;
        $this->application = $engine->application();
    }

    /**
     * Boot the resource
     */
    abstract public function boot();
}
