<?PHP
/**
 * Pi Directed Acyclic Graph Table Gateway
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
 * @subpackage      Table
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Db\Table;

/**
 * Pi Directed Acyclic Graph or Partilly Ordered Set Model
 *
 * Managing Partially Ordered Data with DAG
 * @see http://en.wikipedia.org/wiki/Directed_acyclic_graph
 * @see http://www.codeproject.com/KB/database/Modeling_DAGs_on_SQL_DBs.aspx
 */
abstract class AbstractDag extends AbstractTableGateway
{
    /**
     * Predefined columns
     *
     * @var array
     */
    protected $column = array(
        // Start vertex column name
        'start'     => 'start',
        // End vertex column name
        'end'       => 'end',
        // Entry edge column name
        'entry'     => 'entry',
        // Direct edge column name
        'direct'    => 'direct',
        // Exit edge column name
        'exit'      => 'exit',
        // Number of hops from start to end
        'hops'      => 'hops',
    );

    /**
     * Class for row gateway
     * @var string
     */
    protected $rowClass = 'Pi\Db\RowGateway\Vertex';

    /**
     * Setup model
     * @param array $options
     */
    public function setup($options = array())
    {
        foreach (array_keys($this->column) as $key) {
            if (isset($options[$key])) {
                $this->column[$key] = (string) $options[$key];
                unset($options[$key]);
            }
        }
        parent::setup($options);
    }

    public function initialize()
    {
        if ($this->initialized == true) {
            return;
        }
        parent::initialize();

        $rowObject = $this->resultSetPrototype->getArrayObjectPrototype();
        if (is_callable(array($rowObject, 'setTableGateway'))) {
            $rowObject->setTableGateway($this);
        }
    }

    public function column($column)
    {
        return isset($this->column[$column]) ? $this->column[$column] : null;
    }
}
