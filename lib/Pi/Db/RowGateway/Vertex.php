<?PHP
/**
 * Pi Vertex Row Gateway
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
 * @package         Pi\Db
 * @subpackage      RowGateway
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Db\RowGateway;
use Pi\Db\Table\AbstractTableGateway;

/**
 * Vertex row gateway class for DAG rowset
 * @see \Pi\Db\Table\AbstractDag
 */
class Vertex extends RowGateway
{
    /**
     * Table Gateway
     * @var AbstractTableGateway
     */
    protected $tableGateway;

    public function setTableGateway(AbstractTableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Magic method to access properties
     *
     * @param  string $name
     * @return type
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            $key = $this->tableGateway->column($name);
            if ($key && array_key_exists($key, $this->data)) {
                return $this->data[$key];
            }
            throw new \InvalidArgumentException('Not a valid column in this row: ' . $name);
        }
    }
}
