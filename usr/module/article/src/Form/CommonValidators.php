<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi;

/**
 * Validator configuration
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CommonValidators
{
    /**
     * Set subject length validator
     * 
     * @return array
     */
    public static function subjectValidator()
    {
        $module = Pi::service('module')->current();
        $length = Pi::config('max_subject_length', $module);
        
        return array(
            new \Zend\Validator\StringLength(array(
                'max'       => $length,
                'encoding'  => 'utf-8',
            ))
        );
    }
    
    /**
     * Set subtitle length validator
     * 
     * @return array
     */
    public static function subtitleValidator()
    {
        $module = Pi::service('module')->current();
        $length = Pi::config('max_subtitle_length', $module);
        
        return array(
            new \Zend\Validator\StringLength(array(
                'max'       => $length,
                'encoding'  => 'utf-8',
            ))
        );
    }
    
    /**
     * Set summary length validator
     * 
     * @return array
     */
    public static function summaryValidator()
    {
        $module = Pi::service('module')->current();
        $length = Pi::config('max_summary_length', $module);
        
        return array(
            new \Zend\Validator\StringLength(array(
                'max'       => $length,
                'encoding'  => 'utf-8',
            ))
        );
    }
}
