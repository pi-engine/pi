<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Template element class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Template extends Select
{
    /**
     * Custom template path 
     */
    const TEMPLATE_PATH = 'article/template/front';
    
    /**
     * Custom template format 
     */
    const TEMPLATE_FORMAT = '/^topic-custom-(.+)/';
    
    /**
     * Resolving select options
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $path      = sprintf(
                '%s/%s', 
                rtrim(Pi::path('module'), '/'), 
                self::TEMPLATE_PATH
            );
            $iterator  = new \DirectoryIterator($path);
            $templates = array('default' => __('Default'));
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile()) {
                    continue;
                }
                $filename = $fileinfo->getFilename();
                $name     = substr($filename, 0, strrpos($filename, '.'));
                if (!preg_match(self::TEMPLATE_FORMAT, $name, $matches)) {
                    continue;
                }
                $displayName = preg_replace('/[-_]/', ' ', $matches[1]);
                $templates[$name] = ucfirst($displayName);
            }
            $this->valueOptions = $templates;
        }

        return $this->valueOptions;
    }
}
