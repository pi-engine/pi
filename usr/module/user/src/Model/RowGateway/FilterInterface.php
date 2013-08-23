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
interface FilterInterface
{
    /**
     * Filter value of column(s) for display purposes
     *
     * @param string|string[] $col
     * @return mixed|mixed[]
     */
    public function filter($col = null);
}
