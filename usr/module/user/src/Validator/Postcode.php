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
 * Validator for postcode
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */

class Postcode extends AbstractValidator
{
    const POSTCODE_INVALID = 'postcodeInvalid';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::POSTCODE_INVALID => 'The input is not 6 numeric',
    );

    public function isValid($value)
    {
        if (is_numeric($value) &&  strlen($value) == 6) {
            return ture;
        } else {
            $this->error(static::POSTCODE_INVALID);
            return false;
        }
    }
}