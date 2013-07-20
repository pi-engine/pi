<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

class User extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $identity = (string) Pi::service('authentication')->getIdentity();
        Pi::service('user')->bind($identity, 'identity');
        Pi::registry('user', Pi::service('user')->getUser());
    }
}
