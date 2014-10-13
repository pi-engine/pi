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
    protected $identifier = 'category';
    
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
        $rows   = Pi::registry($this->identifier, $module)->read($plain, $module);
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
     * Get single detail by ID or slug
     * @param int|string  $id  Category ID or unique slug
     * @return array
     */
    public function get($id)
    {
        if (is_numeric($id)) {
            $where['id'] = $id;
        } else {
            $where['slug'] = $id;
        }
        $rowset = $this->getList($where);
        $result = $rowset ? array_shift($rowset) : array();
        
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
    
    /**
     * Get ids of all children
     *
     * @param int       $id           Node id
     * @param null      $cols         Columns, null for all
     * @param bool      $includeSelf  Include self in result or not
     * @return array Node ids
     */
    public function getDescendantIds(
        $id,
        $includeSelf = true
    ) {
        $current = $this->getList(array('id' => $id));
        if (empty($current)) {
            return array();
        }
        
        $result   = array();
        $current  = array_shift($current);
        $children = $this->getList(array(
            'left >= ?'  => $current['left'],
            'right <= ?' => $current['right'],
        ));
        if ($children) {
            foreach ($children as $item) {
                if (!$includeSelf && $id == $item['id']) {
                    continue;
                }
                $result[] = intval($item['id']);
            }
        }
        
        return $result;
    }
    
    /**
     * Get category data with format support assembling navigation
     * 
     * @param array  $options
     * @param bool   $withAll
     * @return array
     */
    public function navigation($options = array(), $withAll = true)
    {
        $module  = $this->getModule();
        $route   = Pi::api('api', $module)->getRouteName();
        $default = array(
            'module'     => $module,
            'controller' => 'list',
            'action'     => 'index',
            'route'      => $route,
        );
        
        $options = array_merge($default, $options);
        
        $rowset     = $this->getList(array(), null, false);
        $navigation = $this->canonizeNav($rowset['child'], $options);
        
        if ($withAll) {
            $all['all'] = array_merge($options, array(
                'label'      => __('All'),
                'params'     => array(
                    $this->identifier => 'all',
                ),
            ));
            $navigation = $all + $navigation;
        }
        
        return $navigation;
    }
    
    /**
     * Canonize category structure
     * 
     * @params array  $items
     * @params array  $options
     */
    protected function canonizeNav(&$items, $options = array())
    {
        foreach ($items as $key => &$row) {
            if (!$row['active']) {
                unset($items[$key]);
                continue;
            }
            $row['label']  = $row['title'];
            $row['params'] = array(
                $this->identifier => $row['slug'] ?: $row['id'],
            );
            $row = array_merge($row, $options);
            if (isset($row['child'])) {
                $row['pages'] = $row['child'];
                unset($row['child']);
                $this->canonizeNav($row['pages'], $options);
            }
        }
        
        return $items;
    }
}