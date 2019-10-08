<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Model;

use Pi;
use Pi\Application\Model\Model;

/**
 * Extended model class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Extended extends Model
{
    /**
     * Get valid columns direct from table
     *
     * @return boolean|array
     */
    public function getValidColumns()
    {
        $table    = $this->getTable();
        $database = Pi::config()->load('service.database.php');
        $schema   = $database['schema'];
        $sql      = 'select COLUMN_NAME as name from information_schema.columns'
            . ' where table_name=\'' . $table . '\' and table_schema=\''
            . $schema . '\'';
        try {
            $rowset = Pi::db()->getAdapter()->query($sql, 'prepare')->execute();
        } catch (\Exception $exception) {
            return false;
        }

        $fields = [];
        foreach ($rowset as $row) {
            if (in_array($row['name'], ['id', 'article'])) {
                continue;
            }
            $fields[] = $row['name'];
        }

        return $fields;
    }

    /**
     * Change article slug to article ID
     *
     * @param string $slug
     * @return int
     */
    public function slugToId($slug)
    {
        $result = false;

        if ($slug) {
            $row = $this->find($slug, 'slug');
            if ($row) {
                $result = $row->article;
            }
        }

        return $result;
    }
}
