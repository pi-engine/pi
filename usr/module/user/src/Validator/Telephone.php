<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Validator for telephone
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */

class Telephone extends AbstractValidator
{
    const TELEPHONE_INVALID = 'telephoneInvalid';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::TELEPHONE_INVALID => 'The input is not 7-20 numeric',
    );

    public function isValid($value)
    {
        if (is_numeric($value) &&
            strlen($value) >= 7 &&
            strlen($value) <= 20
        ) {
            return ture;
        } else {
            $this->error(static::TELEPHONE_INVALID);
            return false;
        }
    }
}