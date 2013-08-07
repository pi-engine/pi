<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\Acl;

use Pi;
use Pi\Application\Model\Model;
use Zend\Db\Sql\Select;

/**
 * ACL role model
 *
 * The model follows DAG (Directed Acyclic Graph),
 * i.e. one role could inherit from multiple parent roles
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Role extends Model
{
    /** @var string Table name */
    protected $table = 'acl_role';

    /**
     * Gets all ancestors of a role
     *
     * @param string $role
     * @return array
     */
    public function getAncestors($role)
    {
        $parents    = array();
        $model      = Pi::model('acl_inherit');
        $rowset     = $model->select(array('child' => $role));

        foreach ($rowset as $row) {
            $parents[]  = $row->parent;
            $sub        = $this->getAncestors($row->parent);
            $parents    = array_unique(array_merge($parents, $sub));
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
            $sub        = $this->getChildren($row->child);
            $children   = array_unique(array_merge($children, $sub));
        }

        return $children;
    }
}
