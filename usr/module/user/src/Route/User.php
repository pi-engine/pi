<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
    protected $defaults
        = [
            'module'     => 'user',
            'controller' => 'index',
            'action'     => 'index',
        ];

    public function assemble(array $params = [], array $options = [])
    {
        $url = parent::assemble($params, $options);

        $finalUrl = rtrim($url, '/');

        return $finalUrl;
    }
}