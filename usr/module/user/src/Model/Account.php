<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
