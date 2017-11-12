<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Model;

use Pi\Application\Model\Model as BasicModel;

/**
 * Widget model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Widget extends BasicModel
{
    /**
     * {@inheritDoc}
     */
    protected $encodeColumns = array(
        //'meta'  => true,
    );
}
