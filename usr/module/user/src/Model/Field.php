<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Model;

use Pi\Application\Model\Model as BasicModel;

/**
 * User field model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Field extends BasicModel
{
    /**
     * Columns to be encoded
     *
     * @var array
     */
    protected $encodeColumns
        = [
            'edit'   => true,
            'filter' => true,
        ];
}
