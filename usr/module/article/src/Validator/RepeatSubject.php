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
 * Repeat subject valid class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class RepeatSubject extends AbstractValidator
{
    const SUBJECT_EXISTS = 'subjectExists';

    /**
     * @var array
     */
    protected $messageTemplates = array();

    /**
     * Constructor, tanslate the message
     * 
     * @param array $options
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = array(
            self::SUBJECT_EXISTS => __('Subject is used by another article.'),
        );
        parent::__construct($options);
    }
    
    /**
     * Check whether a subject is repeat
     *
     * @param  mixed  $value
     * @param  array  $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $module  = Pi::service('module')->current();
        $where   = array(
            'subject' => $value,
        );
        if ($context['article']) {
            $where['id <> ?'] = $context['article'];
        }
        $count = Pi::model('article', $module)->count($where);
        if (empty($count)) {
            return true;
        }
        
        $this->error(self::SUBJECT_EXISTS);
        return false;
    }
}
