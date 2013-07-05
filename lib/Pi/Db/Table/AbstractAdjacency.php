<?PHP
/**
 * Pi Adjacency List Table Gateway
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
 * Pi Adjacency List Model
 *
 * Managing Hierarchical Data with adjacency list model
 */
abstract class AbstractAdjacency extends AbstractTableGateway
{
    /**
     * Predefined columns
     *
     * @var array
     */
    protected $column = array(
        'parent'    => 'parent',
    );

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

    public function column($column)
    {
        return isset($this->column[$column]) ? $this->column[$column] : null;
    }

    /**
     * Gets all ancestors of a role
     *
     * @todo Not ready yet
     */
    public function getAncestors($role)
    {
        $parents = array();
        $select = $this->getAdapter()->select()
                    ->from(array('r' => $this->_name))
                    ->where(array('r.active' => 1))
                    ->joinLeft(array('i' => $this->getAdapter()->prefix('acl_inherit', 'xo')), 'r.name = i.parent')
                    ->where(array('i.child' => $role));
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
