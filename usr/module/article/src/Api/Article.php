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
class Article extends AbstractApi
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
            $class = sprintf('Custom\Article\Field\%s', ucfirst($field));
            if (!class_exists($class)) {
                $class = sprintf('Module\Article\Field\%s', ucfirst($field));
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
}