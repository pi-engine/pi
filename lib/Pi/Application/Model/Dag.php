<?php
/**
 * Pi DAG Table Gateway Model
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Model
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Model;
use Pi\Db\Table\AbstractDag;

class Dag extends AbstractDag
{
    /**
     * Primary key, required for model
     *
     * @var string
     */
    protected $primaryKeyColumn = 'id';

    /**
     * Predefined columns
     *
     * @var array
     */
    protected $column = array(
        // Start vertex column name
        "start"     => "start",
        // End vertex column name
        "end"       => "end",
        // Entry edge column name
        "entry"     => "entry",
        // Direct edge column name
        "direct"    => "direct",
        // Exit edge column name
        "exit"      => "exit",
        // Number of hops from start to end
        "hops"      => "hops",
    );
}
