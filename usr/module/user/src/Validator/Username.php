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
use Module\System\Validator\Username as SystemUsername;

/**
 * Validator for username
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Username extends SystemUsername
{
    const TOOSHORT = 'stringLengthTooShort';
    const TOOLONG  = 'stringLengthTooLong';

    protected $messageVariables = array(
        'formatHint' => 'formatHint',
        'max'        => 'max',
        'min'        => 'min',
    );

    protected $formatHint;
    protected $max;
    protected $min;

    public function __construct()
    {
        $this->messageTemplates = array(
            self::INVALID   => __('Invalid user name: %formatHint%'),
            self::RESERVED  => __('Username is reserved'),
            self::TAKEN     => __('Username is already taken'),
            self::TOO_SHORT => __('Username is less than %min% characters long'),
            self::TOO_LONG  => __('Username is more than %max% characters long')
        );

        $this->formatMessage = array(
            'strict'    => __('Only alphabetic and digits are allowed with leading alphabetic'),
            'medium'    => __('Only ASCII characters are allowed'),
            'loose'     => __('Multibyte characters are allowed'),
        );

        parent::__construct();
    }

    /**
     * User name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setConfigOption();
        $this->setValue($value);

        if ($this->options['max']) {
            if ($this->options['max'] < strlen($value)) {
                $this->max = $this->options['max'];
                $this->error(static::TOOLONG);
                return false;
            }
        }
        if ($this->options['min']) {
            if ($this->options['min'] > strlen($value)) {
                $this->min = $this->options['min'];
                $this->error(static::TOOSHORT);
                return false;
            }
        }

        $result = parent::isValid($value, $context);

        return $result;
    }

    /**
     * Set username validator according to config
     *
     * @return $this
     */
    public function setConfigOption()
    {
        $this->options = array(
            'min'       => Pi::user()->config('uname_min'),
            'max'       => Pi::user()->config('uname_max'),
            'format'    => Pi::user()->config('uname_format'),
            'backlist'  => Pi::user()->config('uname_backlist'),
            'format'    => Pi::user()->config('uname_format'),
            'checkDuplication' => true,
        );

        return $this;
    }
}
