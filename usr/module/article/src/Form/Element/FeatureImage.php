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

/**
 * Media element class for operate article feature image
 * 
 * <Options>
 * `urls`:       array, @see description of $urls variable
 * - `custom_save`: save image into feature folder and return image path
 * - `custom_remove`: remove image from feature folder
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class FeatureImage extends Media
{
    /**
     * Set AJAX url for operating media, set default url if current url is empty
     * 
     * @param array $data
     */
    public function setAjaxUrls($data = array())
    {
        $data = array_merge($this->urls, (array) $data);
        
        // Added default custom save and remove image method
        if (!isset($data['custom_save']) || empty($data['custom_save'])) {
            $size = $this->getOption('size');
            $data['custom_save'] = Pi::service('url')->assemble(
                '', 
                array(
                    'controller' => 'ajax',
                    'action'     => 'save-media',
                    'name'       => 'feature',
                    'width'      => isset($size['width']) ? $size['width'] : 0,
                    'height'     => isset($size['height']) ? $size['height'] : 0,
                )
            );
        }
        
        /*if (!isset($data['custom_remove']) || empty($data['custom_remove'])) {
            $data['custom_remove'] = Pi::service('url')->assemble(
                '',
                array(
                    'controller' => 'ajax',
                    'action'     => 'custom',
                    'method'     => 'remove-image',
                    'name'       => 'feature',
                )
            );
        }*/
        
        parent::setAjaxUrls($data);
    }
    
    /**
     * Change image URL into readable data for template
     * 
     * @param string $value
     * @return array
     */
    public function canonizeMedias($value = '')
    {
        $value  = $value ?: $this->getValue();
        
        if (empty($value)) {
            return array();
        }
        
        $result = array();
        
        if (is_numeric($value)
            || !file_exists(Pi::path($value))
        ) {
            return $result;
        }
        
        $basename = basename($value);
        $title    = substr($basename, 0, strrpos($basename, '.'));
        $result[] = array(
            'id'       => $value,
            'url'      => Pi::url($value),
            'title'    => $title,
            'download' => '',
        );
        
        return $result;
    }
}
