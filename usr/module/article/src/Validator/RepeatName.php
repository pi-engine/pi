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
    protected $messageTemplates = array();
    
    /**
     * Initialize template
     * 
     * @param array $options
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = array(
        self::NAME_EXISTS => __('The name is already exists in database!'),
        );
        
        parent::__construct($options);
    }

    /**
     * Check whether a name is repeat
     *
     * @param  mixed  $value
     * @param  array  $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $module  = Pi::service('module')->current();
        $options = $this->getOptions();
        $where   = array(
            'name' => $value,
        );
        if ($context['id']) {
            $where['id <> ?'] = $context['id'];
        }
        $count = Pi::model($options['table'], $module)->count($where);
        if (empty($count)) {
            return true;
        }
        
        $this->error(self::NAME_EXISTS);
        return false;
    }
}
