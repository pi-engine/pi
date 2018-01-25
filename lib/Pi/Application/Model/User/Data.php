<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
    protected $encodeColumns
        = [
            'value_multi' => true,
        ];
}
