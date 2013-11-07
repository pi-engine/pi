<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

/**
 * User load
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        /*
        $identity = Pi::service('authentication')->getIdentity();
        Pi::service('user')->bind($identity, 'identity');
        */
        //Pi::service('user')->bind();

        Pi::service('authentication')->bind();
    }
}
