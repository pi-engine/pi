<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model\RowGateway;

use Pi;

/**
 * User profile row gateway display interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
interface DisplayInterface
{
    /**
     * Get value of a column or all columns for display
     *
     * @param string $col
     * @return string|mixed[]
     */
    public function display($col = null);
}
