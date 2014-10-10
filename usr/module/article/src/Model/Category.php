<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Model;

use Pi;
use Pi\Application\Model\Nest as Nest;

/**
 * Category model class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Category extends Nest
{
    /**
     * Get table fields exclude id field.
     * 
     * @return array 
     */
    public function getColumns($fetch = false)
    {
        $table    = $this->getTable();
        $database = Pi::config()->load('service.database.php');
        $schema   = $database['schema'];
        $sql = 'select COLUMN_NAME as name from information_schema.columns '
             . 'where table_name=\'' 
             . $table . '\' and table_schema=\'' 
             . $schema . '\'';
        try {
            $rowset = Pi::db()->getAdapter()->query($sql, 'prepare')->execute();
        } catch (\Exception $exception) {
            return false;
        }
        
        $fields = array();
        foreach ($rowset as $row) {
            if ($row['name'] == 'id') {
                continue;
            }
            $fields[] = $row['name'];
        }
        
        return $fields;
    }

    /**
     * Get nodes by ids
     *
     * @param array  $ids      Node ids
     * @param null   $columns  Columns, null for default
     * @return array
     */
    public function getRows($ids, $columns = null)
    {
        $result = $rows = array();

        if (null === $columns) {
            $columns = $this->getColumns();
        }

        if (!in_array('id', $columns)) {
            $columns[] = 'id';
        }

        if ($ids) {
            $result = array_flip($ids);

            $select = $this->select()
                ->columns($columns)
                ->where(array('id' => $ids));

            $rows = $this->selectWith($select)->toArray();

            foreach ($rows as $row) {
                $result[$row['id']] = $row;
            }

            $result = array_filter($result, function($var) {
                return is_array($var);
            });
        }

        return $result;
    }

    /**
     * Get nodes in level N
     *
     * @param int   $depth    Level depth
     * @param null  $columns  Columns, null for default
     * @return array Associative array
     */
    public function getLevel($depth, $columns = null)
    {
        $result = $rows = array();

        if (null === $columns) {
            $columns = $this->getColumns();
        }

        if (!in_array('id', $columns)) {
            $columns[] = 'id';
        }

        $select = $this->select()
            ->columns($columns)
            ->where(array(
                'depth' => intval($depth),
            ))
            ->order('left ASC');
        $rows = $this->selectWith($select)->toArray();

        foreach ($rows as $row) {
            $result[$row['id']] = $row;
        }

        unset($rows);

        return $result;
    }

    /**
     * Get direct parent node info
     *
     * @param int|Node $objective  Node id
     * @param null     $cols       Columns, null for all
     * @return bool|array Parent node info
     */
    public function getParentNode($objective, $cols = null)
    {
        $row = $this->normalizeNode($objective);
        if (!$row) {
            return false;
        }
        $select = $this->select()
            ->where(array($this->quoteColumn('left') . ' < ?' => $row->left))
            ->where(array($this->quoteColumn('right') . ' > ?' => $row->right));
        if (!empty($cols)) {
            $select->columns($cols);
        }
        $select->order($this->column['left'] . ' DESC')->limit(1);
        if (!$rowset = $this->selectWith($select)) {
            return false;
        }

        $result = array();
        foreach ($rowset as $row) {
            $result = $row->toArray();
        }

        return $result;
    }

    /**
     * Check whether a node have children
     *
     * @param int|Node  $objective  Node id
     * @return bool
     */
    public function hasChildren($objective)
    {
        $row = $this->normalizeNode($objective);
        if (!$row) {
            return false;
        }

        return $row->right - $row->left > 1;
    }

    /**
     * Get ids of all children
     *
     * @param int|Node  $objective    Node id
     * @param null      $cols         Columns, null for all
     * @param bool      $includeSelf  Include self in result or not
     * @return array Node ids
     */
    public function getDescendantIds(
        $objective,
        $cols = null,
        $includeSelf = true
    ) {
        $result = array();

        $children = $this->getChildren($objective, $cols);
        if ($children) {
            foreach ($children as $category) {
                if (!$includeSelf && $objective == $category->id) {
                    continue;
                }
                $result[] = intval($category->id);
            }
        }

        return $result;
    }

    /**
     * Get ids of all sons
     *
     * @param int|Node  $objective    Node id
     * @param null      $cols         Columns, null for all
     * @param bool      $includeSelf  Include self in result or not
     * @return array Node ids
     */
    public function getChildrenIds(
        $objective,
        $cols = null,
        $includeSelf = false
    ) {
        $result = array();

        if (false === array_search('depth', $cols)) {
            $cols[] = 'depth';
        }

        $self = $this->normalizeNode($objective);
        $children = $this->getChildren($objective, $cols);
        foreach ($children as $category) {
            if (!$includeSelf && $objective == $category->id) {
                continue;
            }
            if ($category->depth == $self->depth + 1) {
                $result[] = intval($category->id);
            }
        }

        return $result;
    }
    
    /**
     * Remove un-exists fields
     * 
     * @param array $data
     * @return array
     */
    public function canonizeColumns($data)
    {
        $columns = $this->getColumns(true);
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }
}
