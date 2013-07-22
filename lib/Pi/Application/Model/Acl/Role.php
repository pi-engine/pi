<?php
/**
 * Pi ACL Role Model
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model\Acl;

use Pi;
use Pi\Application\Model\Model;
use Zend\Db\Sql\Select;

/**
 * Pi ACL role follows DAG (Directed Acyclic Graph), i.e. one role could inherit from multiple parent roles
 */
class Role extends Model
{
    /** @var string */
    protected $table = 'acl_role';

    /**
     * Gets all ancestors of a role
     *
     * @param string $role
     * @return array
     */
    public function getAncestors($role)
    {
        $parents = array();
        $model = Pi::model('acl_inherit');
        /*
        $table = $model->getTable();
        $select = new Select;
        $select->columns(array('grand' => 'parent'))
                ->from(array('r' => $table))
                ->join(array('c' => $table), 'r.child = c.parent', array('parent' => 'parent'))
                ->where(array('c.child' => $role));

        $statement = $model->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (empty($result)) {
            return $parents;
        }
        */
        $rowset = $model->select(array('child' => $role));

        foreach ($rowset as $row) {
            $parents[] = $row->parent;
            $sub = $this->getAncestors($row->parent);
            $parents = array_unique(array_merge($parents, $sub));
        }
        return $parents;
    }

    /**
     * Gets all children of a role
     *
     * @param string $role
     * @return array
     */
    public function getChildren($role)
    {
        $children = array();
        $model = Pi::model('acl_inherit');
        $rowset = $model->select(array('parent' => $role));

        foreach ($rowset as $row) {
            $children[] = $row->child;
            $sub = $this->getChildren($row->child);
            $children = array_unique(array_merge($children, $sub));
        }
        return $children;
    }
}
