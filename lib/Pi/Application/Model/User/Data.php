<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model\User;

use Pi\Application\Model\Model;

/**
 * User data model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Data extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $encodeColumns = array(
        'value_multi'   => true,
    );
}
