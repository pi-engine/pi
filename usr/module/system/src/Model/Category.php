<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Model;

use Pi\Application\Model\Model;

/**
 * Module category model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Category extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $encodeColumns
        = [
            'modules' => true,
        ];

    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id', 'title', 'icon', 'order', 'modules',
        ];
}
