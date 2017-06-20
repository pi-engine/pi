<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */


namespace Module\User\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Validator for username
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

class Password extends AbstractValidator
{
    const TOO_SHORT = 'stringLengthTooShort';
    const TOO_LONG  = 'stringLengthTooLong';
    const UPPER  = 'upper';
    const LOWER  = 'lower';
    const DIGIT  = 'digit';

    protected $messageTemplates;

    protected $messageVariables = array(
        'max'        => 'max',
        'min'        => 'min',
    );

    protected $max;
    protected $min;

    public function __construct()
    {
        $this->messageTemplates = array(
            self::TOO_SHORT => __('Password is less than %min% characters long'),
            self::TOO_LONG  => __('Password is more than %max% characters long'),
            self::UPPER  => __("Password must contain at least one uppercase letter"),
            self::LOWER  => __("Password must contain at least one lowercase letter"),
            self::DIGIT  => __("Password must contain at least one digit character")
        );

        parent::__construct();
    }

    public function isValid($value)
    {
        $this->setValue($value);
        $this->setConfigOption();

        if (!empty($this->options['max'])
            && $this->options['max'] < strlen($value)
        ) {
            $this->max = $this->options['max'];
            $this->error(static::TOO_LONG);
            return false;
        }
        if (!empty($this->options['min'])
            && $this->options['min'] > strlen($value)
        ) {
            $this->min = $this->options['min'];
            $this->error(static::TOO_SHORT);
            return false;
        }

        $piConfig = Pi::user()->config();
        $strenghtenPassword = $piConfig['strenghten_password'];

        if($strenghtenPassword){
            if (!preg_match('/[A-Z]/', $value)) {
                $this->error(self::UPPER);
                return false;
            }

            if (!preg_match('/[a-z]/', $value)) {
                $this->error(self::LOWER);
                return false;
            }

            if (!preg_match('/\d/', $value)) {
                $this->error(self::DIGIT);
                return false;
            }
        }

        return true;
    }

    /**
     * Set username validator according to config
     *
     * @return $this
     */
    public function setConfigOption()
    {
        $this->options = array(
            'min'       => Pi::user()->config('password_min'),
            'max'       => Pi::user()->config('password_max'),
        );

        return $this;
    }
}