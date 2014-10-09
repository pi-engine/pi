<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * Public API for other module
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Category extends AbstractApi
{
    protected $module = 'article';
    
    /**
     * Table name
     * @var string 
     */
    protected $table = 'category';
    
    /**
     * Read category data from cache
     * 
     * @param array $where
     * @return array 
     */
    public function getList(
        $where = array(),
        $columns = null,
        $plain = true,
        $withRoot = false
    ) {
        $module = $this->getModule();
        $rows   = Pi::registry($this->table, $module)->read($plain, $module);
        if (!$plain) {
            return $rows;
        }
        
        if (!$withRoot) {
            foreach ($rows as $id => $row) {
                if (0 == $row['depth']) {
                    unset($rows[$id]);
                    break;
                }
            }
        }
        
        $result = Pi::api('api', $module)->filterData($rows, $where, $columns);
        
        return $result;
    }
    
    /**
     * Get category ID by slug
     * 
     * @param string $slug
     * @return int
     */
    public function slugToId($slug)
    {
        if (is_numeric($slug)) {
            return $slug;
        }
        
        $rows = $this->getList(array(
            'slug'   => $slug,
            'active' => 1,
        ));
        $row  = array_shift($rows);
        $id   = isset($row['id']) ? $row['id'] : 0;
        
        return $id;
    }
}