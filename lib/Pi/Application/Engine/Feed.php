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
 * Pi feed application engine
 *
 * Tasks:
 *
 * - load configs, default listeners, module manager, bootstrap, application;
 * - boot application;
 * - run application
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Feed extends Standard
{
    /**
     * {@inheritDoc}
     */
    const SECTION = 'feed';

    /**
     * {@inheritDoc}
     */
    protected $fileIdentifier = 'feed';
}
