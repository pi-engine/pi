<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Repeat slug valid class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class RepeatSlug extends AbstractValidator
{
    const SLUG_EXISTS        = 'slugExists';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::SLUG_EXISTS     => 'The slug is already exists in database!',
    );

    /**
     * Check whether a slug is repeat
     *
     * @param  mixed  $value
     * @param  array  $context
     * @return boolean
     */
    public function isValid($value)
    {
        $this->setValue($value);

        $options = $this->getOptions();
        $module  = Pi::service('module')->current();
        $row     = Pi::model($options['table'], $module)->find($value, 'slug');
        if (empty($row)) {
            return true;
        }
        if (isset($options['id']) and $row->id == $options['id']) {
            return true;
        }
        
        $this->error(self::SLUG_EXISTS);
        return false;
    }
}
