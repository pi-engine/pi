<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model;

/**
 * Route model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Route extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $encodeColumns
        = [
            'data' => true,
        ];
}
