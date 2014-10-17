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
class Field extends AbstractApi
{
    /** 
     * Module name
     * @var string
     */
    protected $module = 'article';

    /**
     * Translate data into that can be used in detail page
     * 
     * @param array $data
     * @return array
     */
    public function resolver($data)
    {
        $result = array();
        
        foreach ($data as $field => $value) {
            $name  = $this->canonizeClassName($field);
            $class = sprintf('Custom\%s\Field\%s', ucfirst($this->module), $name);
            if (!class_exists($class)) {
                $class = sprintf('Module\Article\Field\%s', $name);
                if (!class_exists($class)) {
                    $result[$field] = $value;
                    continue;
                }
            }
            $handler = new $class($this->module, $field);
            $result[$field] = $handler->resolve($value, $data);
        }
        
        return $result;
    }
    
    /**
     * Get compound fields name, if the $data param is set, its compound fields
     * will be returned
     * 
     * $data parameter format:
     * array('<compound_field>' => '')
     * 
     * @param array $data  Data want to fetch compound fields
     * @return array
     */
    /*public function getCompoundFields(array $data = array())
    {
        $compound = Pi::registry('field', $this->module)->read('compound');
        if ($data) {
            $compound = array_intersect_key($data, $compound);
        }
        
        return array_keys($compound);
    }*/
    
    /**
     * Load compound field handler
     * 
     * @param string  $name    Field name
     * @param string  $module  Module name
     * @return \Module\Article\Field\AbstractCustomHandler
     */
    public function loadCompoundFieldHandler($name, $module = '')
    {
        $formatName = $this->canonizeClassName($name);
        $module     = $module ?: $this->module;
        $class      = sprintf('Custom\Article\Field\%s', $formatName);
        if (!class_exists($class)) {
            $class = sprintf('Module\Article\Field\%s', $formatName);
            if (!class_exists($class)) {
                throw new \Exception(sprintf('Class %s not exists.', $class));
            }
        }
        $handler = new $class($module, $name);
        
        return $handler;
    }
    
    /*public function getCustomFields(array $data = array())
    {
        $custom = Pi::registry('field', $this->module)->read('custom');
        if ($data) {
            $custom = array_intersect_key($data, $custom);
        }
        
        return array_keys($custom);
    }*/
    
    /**
     * Load custom field handler
     * 
     * @param string  $name    Field name
     * @param string  $module  Module name
     * @return \Module\Article\Field\AbstractCustomHandler
     */
    public function loadCustomFieldHandler($name, $module = '')
    {
        return $this->loadCompoundFieldHandler($name, $module);
    }
    
    /**
     * Format given string to available class name
     * 
     * @param string $name
     * @return string
     */
    protected function canonizeClassName($name)
    {
        return str_replace(' ', '', ucwords(
            str_replace(array('_', '-', '.'), ' ', strtolower($name))));
    }
}