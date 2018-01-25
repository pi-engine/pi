<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Validator;

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

    protected $messageVariables
        = [
            'max' => 'max',
            'min' => 'min',
        ];

    protected $max;
    protected $min;

    public function __construct()
    {
        $this->messageTemplates = [
            self::TOO_SHORT => __('Password is less than %min% characters long'),
            self::TOO_LONG  => __('Password is more than %max% characters long'),
        ];

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

        return true;
    }

    /**
     * Set username validator according to config
     *
     * @return $this
     */
    public function setConfigOption()
    {
        $this->options = [
            'min' => Pi::user()->config('password_min'),
            'max' => Pi::user()->config('password_max'),
        ];

        return $this;
    }
}