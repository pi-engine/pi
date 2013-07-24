<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\User;

use Pi;
use Pi\Application\Model\Model;

/**
 * User repo model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Repo extends Model
{
    /**
     * Columns to be encoded
     *
     * @var array
     */
    protected $encodeColumns = array(
        'content'   => true,
    );
}
