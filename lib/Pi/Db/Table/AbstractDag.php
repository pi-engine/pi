<?PHP
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Db\Table;

/**
 * Pi Directed Acyclic Graph or Partilly Ordered Set Model
 *
 * Managing Partially Ordered Data with DAG
 *
 * @see http://en.wikipedia.org/wiki/Directed_acyclic_graph
 * @see http://www.codeproject.com/KB/database/Modeling_DAGs_on_SQL_DBs.aspx
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractDag extends AbstractTableGateway
{
    /**
     * Predefined columns
     *  - start: Start vertex column name
     *  - end: End vertex column name
     *  - entry: Entry edge column name
     *  - direct: Direct edge column name
     *  - exit: Exit edge column name
     *  - hops: Number of hops from start to end
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
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
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

    /**
     * Get column name
     *
     * @param string $column
     * @return string|null
     */
    public function column($column)
    {
        return isset($this->column[$column]) ? $this->column[$column] : null;
    }
}
