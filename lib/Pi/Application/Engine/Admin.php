<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Engine;

/**
 * Pi admin application engine
 *
 * Tasks:
 *
 * - load configs, default listeners, module manager, bootstrap, application;
 * - boot application;
 * - run application
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
