<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap;

use Pi\Mvc\Application as Application;

/**
 * Abstract class for module bootstraps
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class ModuleBootstrap
{
    /** @var Application Application engine */
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
