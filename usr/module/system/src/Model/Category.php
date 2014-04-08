<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    protected $encodeColumns = array(
        'modules'  => true,
    );

    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id', 'title', 'icon', 'order', 'modules'
    );
}
