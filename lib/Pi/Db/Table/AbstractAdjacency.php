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
 * Pi Adjacency List Table Gateway
 *
 * Managing Hierarchical Data with adjacency list model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdjacency extends AbstractTableGateway
{
    /**
     * Predefined columns
     * @var array
     */
    protected $column = array(
        'parent'    => 'parent',
    );

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
     * Get column name
     *
     * @param string $column
     * @return string|null
     */
    public function column($column)
    {
        return isset($this->column[$column]) ? $this->column[$column] : null;
    }

    /**
     * Gets all ancestors of a role
     *
     * @todo Not ready yet
     * @param int $node
     * @return string[]
     */
    public function getAncestors($node)
    {
        $parents = array();
        $select = $this->getAdapter()->select()
                    ->from(array('r' => $this->_name))
                    ->where(array('r.active' => 1))
                    ->joinLeft(array(
                        'i' =>
                            $this->getAdapter()->prefix('acl_inherit', 'xo')),
                        'r.name = i.parent')
                    ->where(array('i.child' => $node));
                    //->order(array('i.order'));
        $result = $select->query()->fetchAll();
        if (empty($result)) {
            return $parents;
        }
        foreach ($result as $row) {
            $parents += $this->getAncestors($row['name']);
            $parents[] = $row['name'];
        }

        return $parents;
    }
}
