<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Module\User\Api\AbstractActivityCallback;
use Pi;

class UserActivityExternal extends AbstractActivityCallback
{
    /**
     * {@inheritDoc}
     */
    public function get($uid, $limit, $offset = 0)
    {
        Pi::service('url')->redirect(
            Pi::service('url')->assemble('default', [
                'module'     => $this->module,
                'controller' => 'activity',
                'action'     => 'index',
            ])
        );
    }
}
