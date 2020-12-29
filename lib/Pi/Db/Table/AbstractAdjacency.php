<?PHP
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
     *
     * @var array
     */
    protected $column
        = [
            'parent' => 'parent',
        ];

    /**
     * {@inheritDoc}
     */
    public function setup($options = [])
    {
        foreach (array_keys($this->column) as $key) {
            if (isset($options[$key])) {
                $this->column[$key] = (string)$options[$key];
                unset($options[$key]);
            }
        }
        parent::setup($options);
    }

    /**
     * Get column name
     *
     * @param string $column
     *
     * @return string|null
     */
    public function column($column)
    {
        return isset($this->column[$column]) ? $this->column[$column] : null;
    }

    /**
     * Gets all ancestors of a role
     *
     * @param int $node
     *
     * @return string[]
     * @todo Not ready yet
     */
    public function getAncestors($node)
    {
        $parents = [];
        $select  = $this->getAdapter()->select()
            ->from(['r' => $this->_name])
            ->where(['r.active' => 1])
            ->joinLeft(
                [
                    'i' =>
                        $this->getAdapter()->prefix('acl_inherit', 'xo')],
                'r.name = i.parent'
            )
            ->where(['i.child' => $node]);
        //->order(array('i.order'));
        $result = $select->query()->fetchAll();
        if (empty($result)) {
            return $parents;
        }
        foreach ($result as $row) {
            $parents   += $this->getAncestors($row['name']);
            $parents[] = $row['name'];
        }

        return $parents;
    }
}
