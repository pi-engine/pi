<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article;

use Pi;
use Zend\Db\Sql\Expression;
use Module\Article\Model\Article;
use Pi\Mvc\Controller\ActionController;
use Module\Article\Controller\Admin\SetupController as Config;
use Module\Article\Form\DraftEditForm;
use Module\Article\Compiled;
use Module\Article\Media;
use Module\Article\Installer\Resource\Route;

/**
 * Common rule API
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Rule
{
    protected static $module = 'article';
    
    /**
     * Get module resources
     * 
     * @param  bool   $columns  Whether to fetch columns or full resources
     * @return array 
     */
    public static function getResources($column = false)
    {
        $resources = array(
            // Article resources
            __('article')    => array(
                'active'             => __('publish') . '-' . __('active'),
                'publish-edit'       => __('publish') . '-' . __('edit'),
                'publish-delete'     => __('publish') . '-' . __('delete'),
            ),

            // Draft resources
            __('draft')      => array(
                'compose'            => __('draft') . '-' . __('compose'),
                'rejected-edit'      => __('rejected') . '-' . __('edit'),
                'rejected-delete'    => __('rejected') . '-' . __('delete'),
                'pending-edit'       => __('pending') . '-' . __('edit'),
                'pending-delete'     => __('pending') . '-' . __('delete'),
                'approve'            => __('pending') . '-' . __('approve'),
            ),
        );
        
        // Return only valid columns
        $columns = array();
        if ($column) {
            foreach ($resources as $key => $res) {
                foreach (array_keys($res) as $item) {
                    $columns[$key][] = $item;
                }
            }
            
            return $columns;
        }
        
        return $resources;
    }
    
    /**
     * Get user permission according to given category or operation name.
     * The return array has a format such as:
     * array('{Category ID}' => array('{Operation name}' => true));
     * 
     * @param string      $operation  Operation name
     * @param string|int  $category   Category name or ID
     * @param int         $uid
     * @return array
     */
    public static function getPermission(
        $isMine = false, 
        $operation = null, 
        $category = null, 
        $uid = null
    ) {
        $module     = Pi::service('module')->current();
        
        // Get role of current section
        $uid     = $uid ?: Pi::user()->getId();
        $roles   = array_values(Pi::user()->getRole($uid, 'admin'));
        if (empty($roles)) {
            return array();
        }
        
        // Get all categories
        if (is_string($category)) {
            $category = Pi::model('category', $module)->slugToId($category);
        }
        $rowCategories = Pi::api('api', $module)->getCategoryList();
        $categories = array();
        foreach ($rowCategories as $row) {
            $categories[$row['name']] = $row['id'];
        }
        
        // Get all resources
        $allResources = self::getResources();
        $resources  = array();
        foreach ($allResources as $row) {
            $resources = array_merge($resources, array_keys($row));
        }
        
        // Get all rules of current role
        $model = Pi::service('permission')->model();
        $where = array(
            'module'    => $module,
            'role'      => $roles,
        );
        $rowRules = $model->select($where)->toArray();
        
        // Get rules
        $rules = array();
        $resourceRules = array();
        foreach ($rowRules as $row) {
            $resource = preg_replace('/_/', '-', $row['resource']);
            if (in_array($resource, $resources)) {
                if (!empty($operation) and $resource != $operation) {
                    continue;
                }
                $resourceRules[$resource] = true;
            }
        }
        foreach ($rowRules as $row) {
            if (preg_match('/^category-(.+)/', $row['resource'], $matches)
                && in_array($matches[1], array_keys($categories))
            ) {
                if (!empty($category) and $matches[1] != $category) {
                    continue;
                }
                $categoryId = $categories[$matches[1]];
                $rules[$categoryId] = $resourceRules;
            }
        }
        
        // If user operating its own draft, given the edit and delete permission
        if ($isMine) {
            $myRules  = array();
            foreach ($categories as $key) {
                $categoryRule = array();
                if (isset($rules[$key]['compose']) 
                    and $rules[$key]['compose']
                ) {
                    $categoryRule = array(
                        'draft-edit'      => true,
                        'draft-delete'    => true,
                        'pending-edit'    => true,
                        'pending-delete'  => true,
                        'rejected-edit'   => true,
                        'rejected-delete' => true,
                    );
                }
                $myRules[$key] = array_merge(
                    isset($rules[$key]) ? $rules[$key] : array(), 
                    $categoryRule
                );
            }
            $rules = $myRules;
        }
        
        return array_filter($rules);
    }
}
