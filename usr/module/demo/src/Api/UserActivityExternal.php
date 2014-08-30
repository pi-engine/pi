<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Pi;
use Module\User\Api\AbstractActivityCallback;

class UserActivityExternal extends AbstractActivityCallback
{
    /**
     * {@inheritDoc}
     */
    public function get($uid, $limit, $offset = 0)
    {
        Pi::service('url')->redirect(
            Pi::service('url')->assemble('default', array(
                'module'        => $this->module,
                'controller'    => 'activity',
                'action'        => 'index',
            ))
        );
    }
}
