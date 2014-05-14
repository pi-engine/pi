<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
