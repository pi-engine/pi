<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Model;

use Pi;
use Pi\Application\Model\User\Account as AccountUserModel;

/**
 * User account model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Account extends AccountUserModel
{
    /**
     * Row gateway class
     *
     * @var string
     */
    protected $rowClass = 'Module\User\Model\RowGateway\Account';

    /**
     * {@inheritDoc}
     */
    public function setup($options = array())
    {
        $options['prefix'] = Pi::db()->prefix('', 'core');
        parent::setup($options);

        return $this;
    }
}
