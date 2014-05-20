<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model\Block;

use Pi\Application\Model\Model;

/**
 * Block root model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Root extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'name', 'title', 'description', 'render', 'module', 'template',
        'config', 'cache_level', 'type',
    );

    /**
     * {@inheritDoc}
     */
    protected $encodeColumns = array(
        'config'    => true,
    );
}
