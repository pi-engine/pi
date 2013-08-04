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

/**
 * Pi API application engine
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Api extends Standard
{
    /** @var string Section name */
    const SECTION = 'api';

    /**
     * Identifier for file name of option data
     * @var string
     */
    protected $fileIdentifier = 'api';
}
