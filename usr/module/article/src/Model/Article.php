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
use Pi\Application\Model\Model;
use Zend\Db\Sql\Expression;

/**
 * Article model class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Article extends Model
{
    const FIELD_STATUS_PUBLISHED = 11;
    const FIELD_STATUS_DELETED   = 12;

    const FIELD_RELATED_TYPE_OFF    = 0;
    const FIELD_RELATED_TYPE_AUTO   = 1;
    const FIELD_RELATED_TYPE_CUSTOM = 2;

    const FIELD_SEO_SITE_DEFAULT        = 0;
    const FIELD_SEO_TITLE_ARTICLE       = 1;
    const FIELD_SEO_TITLE_CATEGORY      = 2;
    const FIELD_SEO_KEYWORDS_TAG        = 1;
    const FIELD_SEO_KEYWORDS_CATEGORY   = 2;
    const FIELD_SEO_DESCRIPTION_SUMMARY = 1;

    const PAGE_BREAK_PATTERN = '|(<p class="pagebreak page-title">.*?</p>)|is';
    
    /**
     * Get table fields exclude id field.
     * 
     * @return array 
     */
    public function getColumns($fetch = false, $default = false)
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
            if ($default && 'content' == $row['name']) {
                continue;
            }
            $fields[] = $row['name'];
        }
        
        return $fields;
    }
    
    /**
     * Get articles by ids
     *
     * @param  array  $ids      Article ids
     * @param  null   $columns  Columns, null for default
     * @return array
     */
    public function getRows($ids, $columns = null)
    {
        $result = $rows = array();

        if (null === $columns) {
            $columns = $this->getColumns(true, true);
        }

        if ($ids) {
            $result = array_flip($ids);

            $rows = $this->select(array('id' => $ids));

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
     * Return rows by search condition
     * 
     * @param array        $where
     * @param int|null     $limit
     * @param int|null     $offset
     * @param array|null   $columns
     * @param string|null  $order
     * @return array 
     */
    public function getSearchRows(
        $where = array(),
        $limit = null,
        $offset = null,
        $columns = null,
        $order = null
    ) {
        $result = $rows = array();

        if (null === $columns) {
            $columns = $this->getColumns(true, true);
        }
        if (!in_array('id', $columns)) {
            $columns[] = 'id';
        }

        $order = (null === $order) ? 'time_publish DESC' : $order;

        $select = $this->select()->columns($columns);
        if ($where) {
            $select->where($where);
        }
        if ($limit) {
            $select->limit(intval($limit));
        }
        if ($offset) {
            $select->offset(intval($offset));
        }
        if ($order) {
            $select->order($order);
        }

        $rows = $this->selectWith($select);
        foreach ($rows as $row) {
            $result[$row->id] = $row->toArray();
        }

        return $result;
    }

    /**
     * Set status of active field
     * 
     * @param array  $ids
     * @param int    $active
     * @return bool 
     */
    public function setActiveStatus($ids, $active)
    {
        return $this->update(
            array('active' => $active),
            array('id' => $ids)
        );
    }

    /**
     * Check whether subject is already exists in database
     * 
     * @param string  $subject
     * @param int     $id
     * @return bool
     */
    public function checkSubjectExists($subject, $id = null)
    {
        $result = false;

        if ($subject) {
            $select = $this->select()
                ->columns(array('total' => new Expression('count(id)')))
                ->where(array(
                    'subject' => $subject,
                    'status'  => self::FIELD_STATUS_PUBLISHED,
                ));
            if ($id) {
                $select->where(array('id <> ?' => $id));
            }

            $result = $this->selectWith($select)->current()->total > 0;
        }

        return $result;
    }
    
    /**
     * Remove un-exist columns
     * 
     * @param array $data
     * @return mixed
     */
    public function canonizeColumns(&$data)
    {
        $data    = (array) $data;
        $columns = $this->getColumns(true);
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }
    }
}
