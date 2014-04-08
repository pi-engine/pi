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

/**
 * Related model class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Related extends Model
{
    /**
     * Get default table fields
     * 
     * @return array 
     */
    public static function getDefaultColumns()
    {
        return array('id', 'related');
    }

    /**
     * Save related article data into table
     * 
     * @param int    $article
     * @param array  $data
     * @return null 
     */
    public function saveRelated($article, $data)
    {
        // Delete old related articles
        $this->delete(array('article' => $article));

        // Insert new related articles
        $order = 0;
        foreach ($data as $relatedId) {
            $row = $this->createRow(array(
                'article' => $article,
                'related' => $relatedId,
                'order'   => $order++,
            ));
            $row->save();
        }

        return;
    }

    /**
     * Get related articles
     * 
     * @param int  $article
     * @return array 
     */
    public function getRelated($article)
    {
        $result = array();

        $select = $this->select()
            ->columns(self::getDefaultColumns())
            ->where(array('article' => $article))
            ->order('order ASC');
        $resultset = $this->selectWith($select)->toArray();

        foreach ($resultset as $row) {
            $result[] = $row['related'];
        }

        return $result;
    }
}
