<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Route;

use Module\System\Route\User as UserRoute;

/**
 * User route
 *
 * {@inheritDoc}
 * - Extended URLs:
 *   - Activity: /activity/name/<activity-name>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends UserRoute
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'user',
        'controller'    => 'index',
        'action'        => 'index'
    );
}