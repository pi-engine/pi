<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
