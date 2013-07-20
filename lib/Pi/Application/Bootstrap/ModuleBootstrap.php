<?php
/**
 * Pi module bootstrap abstraction
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap;

use Pi\Mvc\Application as Application;

/**
 * Abstract class for module bootstraps
 */
abstract class ModuleBootstrap
{
    /** @var Application */
    protected $application;

    /**
     * Constructor
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Boot a module
     *
     * @param string|null $module
     */
    abstract public function bootstrap($module = null);
}
