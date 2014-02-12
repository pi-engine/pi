<?PHP
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Db\RowGateway;

use Pi\Db\Table\AbstractTableGateway;

/**
 * Vertex row gateway class for DAG rowset
 *
 * @see Pi\Db\Table\AbstractDag
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Vertex extends RowGateway
{
    /**
     * Table Gateway
     * @var AbstractTableGateway
     */
    protected $tableGateway;

    /**
     * Set table gateway
     *
     * @param AbstractTableGateway $tableGateway
     * @return void
     */
    public function setTableGateway(AbstractTableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Magic method to access properties
     *
     * @param  string $name
     *
     * @throws \InvalidArgumentException
     * @return mixed
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
            throw new \InvalidArgumentException(
                'Not a valid column in this row: ' . $name
            );
        }
    }
}
