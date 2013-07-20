<?php
/**
 * Feed application engine class
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Engine;

/**
 * Pi feed application engine
 *
 * Tasks: load configs, default listeners, module manager, bootstrap, application; boot application; run application
 */
class Feed extends Standard
{
    /** @var string Section name */
    const SECTION = 'feed';

    /**
     * Identifier for file name of option data
     * @var string
     */
    protected $fileIdentifier = 'feed';
}
