<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Model;

use Pi\Application\Model\Model as BasicModel;

/**
 * Article field model
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Field extends BasicModel
{
    /**
     * Columns to be encoded
     *
     * @var array
     */
    protected $encodeColumns = array(
        'edit'      => true,
        'filter'    => true,
    );
}
