<?php
/**
 * Admin application engine class
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Engine;

/**
 * Pi admin application engine
 *
 * Tasks: load configs, default listeners, module manager, bootstrap, application; boot application; run application
 */
class Admin extends Standard
{
    /** @var string Section name */
    const SECTION = 'admin';

    /**
     * Identifier for file name of option data
     * @var string
     */
    protected $fileIdentifier = 'admin';
}
