<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Engine;

/**
 * Pi API application engine
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Api extends Standard
{
    /**
     * {@inheritDoc}
     */
    const SECTION = 'api';

    /**
     * {@inheritDoc}
     */
    protected $fileIdentifier = 'api';
}
