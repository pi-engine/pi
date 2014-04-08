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
 * Empty valid class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class NotEmpty extends AbstractValidator
{
    const IS_EMPTY        = 'isEmpty';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::IS_EMPTY     => 'The value is required!',
    );

    /**
     * Empty value validate
     *
     * @param  mixed  $value
     * @param  array  $context
     * @return boolean
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (empty($value)) {
            $this->error(self::IS_EMPTY);
            return false;
        }

        return true;
    }
}
