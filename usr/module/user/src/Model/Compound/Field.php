<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model\Compound;

use Pi\Application\Model\Model as BasicModel;

/**
 * User compound field model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Field extends BasicModel
{
    /**
     * Row gateway class
     *
     * @var string
     */
    protected $rowClass = 'Module\User\Model\RowGateway\Compound';

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
