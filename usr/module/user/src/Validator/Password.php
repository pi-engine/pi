<?php
/**
 * User name validator
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\User
 * @subpackage      Validator
 * @version         $Id$
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

    protected $messageTemplates;

    public function __construct()
    {
        $this->messageTemplates = array(
            self::TOO_SHORT => __('Password is less than %min% characters long'),
            self::TOO_LONG  => __('Password is more than %max% characters long'),
        );

        parent::__construct();
    }

    protected $messageVariables = array(
        'max'        => 'max',
        'min'        => 'min',
    );

    protected $max;
    protected $min;

    public function isValid($value)
    {
        $this->setValue($value);
        $this->setConfigOption();

        if ($this->options['max']) {
            if ($this->options['max'] < strlen($value)) {
                $this->max = $this->options['max'];
                $this->error(static::TOO_LONG);
                return false;
            }
        }
        if ($this->options['min']) {
            if ($this->options['min'] > strlen($value)) {
                $this->min = $this->options['min'];
                $this->error(static::TOO_SHORT);
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