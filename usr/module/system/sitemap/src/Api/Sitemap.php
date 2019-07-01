<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Sitemap\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Zend\Validator\Uri as UriValidator;

/**
* Pi::api('sitemap', 'sitemap')->singleLink($loc, $status, $module, $table, $item);
* Pi::api('sitemap', 'sitemap')->groupLink($loc, $status, $module, $table, $item);
* Pi::api('sitemap', 'sitemap')->remove($loc);
* Pi::api('sitemap', 'sitemap')->removeAll($module, $table);
*/
class Sitemap extends AbstractApi
{ 
    /**
    * Old method , will remove
    * Add new link to url_list table
    * 
    * @param  string $module
    * @param  string $table
    * @param  int    $item
    * @param  string  $loc
    */
    public function add($module, $table, $item, $loc, $status = 1)
    {
        $this->singleLink($loc, $status, $module, $table, $item);
    }

    /**
    * Old method , will remove
    * Update link to url_list table
    * 
    * @param  string $module
    * @param  string $table
    * @param  int    $item
    * @param  string  $loc
    */
    public function update($module, $table, $item, $loc, $status = 1)
    {
        $this->singleLink($loc, $status, $module, $table, $item);
    }

    /**
    * Add or Update link to url_list table
    * 
    * @param  string  $loc
    * @param  int     $status
    * @param  string  $module
    * @param  string  $table
    * @param  int     $item
    */
    public function singleLink($loc, $status = 1, $module = '', $table = '', $item = '')
    {
        // Check loc not empty
        if (empty($loc)) {
            return '';
        }
        // Check loc is valid
        $validator = new UriValidator;
        if (!$validator->isValid($loc)) {
            return '';
        }
        // Check loc exist or not
        $row = Pi::model('url_list', 'sitemap')->find($loc, 'loc');
        if (!empty($row) && is_object($row)) {
            $row->loc = $loc;
            $row->lastmod = date("Y-m-d H:i:s");
            $row->status = intval($status);
            $row->save();
        } else {
            // Set
            $values = array();
            $values['loc'] = $loc;
            $values['lastmod'] = date("Y-m-d H:i:s");
            $values['changefreq'] = 'daily';
            $values['priority'] = '';
            $values['time_create'] = time();
            $values['module'] = $module;
            $values['table'] = $table;
            $values['item'] = intval($item);
            $values['status'] = intval($status);
            // Save
            $row = Pi::model('url_list', 'sitemap')->createRow();
            $row->assign($values);
            $row->save();
        }
    }

    /**
    * Add group of links to url_list table whitout check is exist or not
    * 
    * @param  string  $loc
    * @param  int     $status
    * @param  string  $module
    * @param  string  $table
    * @param  int     $item
    */
    public function groupLink($loc, $status = 1, $module = '', $table = '', $item = '')
    {
        // Check loc not empty
        if (empty($loc)) {
            return '';
        }
        // Check loc is valid
        $validator = new UriValidator;
        if (!$validator->isValid($loc)) {
            return '';
        }
        // Set
        $values = array();
        $values['loc'] = $loc;
        $values['lastmod'] = date("Y-m-d H:i:s");
        $values['changefreq'] = 'daily';
        $values['priority'] = '';
        $values['time_create'] = time();
        $values['module'] = $module;
        $values['table'] = $table;
        $values['item'] = intval($item);
        $values['status'] = intval($status);
        // Save
        $row = Pi::model('url_list', 'sitemap')->createRow();
        $row->assign($values);
        $row->save();
    }

    /**
    * Remove link from url_list table
    * 
    * @param  string  $loc
    */
    public function remove($loc)
    {
        // Check module
        if (empty($loc)) {
            return '';
        }
        // Remove
        $where = array('loc' => $loc);
        Pi::model('url_list', 'sitemap')->delete($where);
    } 

    /**
    * Remove link from url_list table
    * 
    * @param  string  $module
    * @param  string  $table
    */
    public function removeAll($module, $table = '')
    {
        // Check module
        if (empty($module)) {
            return '';
        }
        // Check table
        if (empty($table)) {
            $where = array('module' => $module);
        } else {
            $where = array('module' => $module, 'table' => $table);
        }
        // Remove
        Pi::model('url_list', 'sitemap')->delete($where);
    }   
}