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
 * Repeat name valid class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class RepeatName extends AbstractValidator
{
    const NAME_EXISTS        = 'nameExists';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::NAME_EXISTS     => 'The name is already exists in database!',
    );

    /**
     * Check whether a name is repeat
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
        $row     = Pi::model($options['table'], $module)->find($value, 'name');
        if (empty($row)) {
            return true;
        }
        if (isset($options['id']) and $row->id == $options['id']) {
            return true;
        }
        
        $this->error(self::NAME_EXISTS);
        return false;
    }
}
