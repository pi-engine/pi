<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form\Element;

use Pi;
use Zend\Form\Element\MultiCheckbox;

/**
 * Get invitation mode form instance
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class InviteMode extends MultiCheckbox
{
    /**
     * Read all mode from custom and module folders
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
            
            $customMode  = $this->getModes($module);
            $defaultMode = $this->getModes($module, false);
            $modes = array_merge($defaultMode, $customMode);
            if (empty($modes)) {
                $modes = array(0 => __('No item available'));
            }

            $this->valueOptions = $modes;
        }

        return $this->valueOptions;
    }
    
    /**
     * Get modes
     * 
     * @param string $module
     * @param bool   $custom
     * @return array
     */
    protected function getModes($module, $custom = true)
    {
        $result = array();
        
        if ($custom) {
            $path = sprintf(
                '%s/module/%s/src/Invite',
                Pi::path('custom'),
                $module
            );
        } else {
            $path = sprintf(
                '%s/%s/src/Invite',
                Pi::path('module'),
                $module
            );
        }
        if (!is_dir($path)) {
            return $result;
        }
        
        $iterator = new \DirectoryIterator($path);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $filename = $fileinfo->getFilename();
            $name     = substr($filename, 0, strrpos($filename, '.'));
            if (strpos($name, 'Abstract') !== false) {
                continue;
            }
            $words = preg_split('/(?=[A-Z])/', $name);
            $words = array_filter($words);
            $key   = strtolower(implode('-', $words));
            $result[$key] = implode(' ', $words);
        }
        
        return $result;
    }
}
