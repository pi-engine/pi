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
 * Model class for operating author table
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Author extends Model
{
    /**
     * Getting available fields
     * 
     * @return array 
     */
    public static function getAvailableFields()
    {
        return array('id', 'name', 'photo', 'description');
    }

    /**
     * Getting author name
     * 
     * @return array 
     */
    public function getSelectOptions()
    {
        $result = array('0' => '');

        $select = $this->sql->select()
            ->columns(array('id', 'name'))->order('name ASC');
        $authors = $this->selectWith($select);

        foreach ($authors as $author) {
            $result[$author->id] = $author->name;
        }

        return $result;
    }
}
