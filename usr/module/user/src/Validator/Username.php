<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
    /** Constants for username length restrict */
    const TOO_SHORT = 'stringLengthTooShort';
    const TOO_LONG  = 'stringLengthTooLong';

    /**
     * Maximum/minimum length of username
     * @var string
     */
    protected $max;
    protected $min;

    /**
     * {@inheritDoc}
     */
    protected $messageVariables = [
        'formatHint' => 'formatHint',
        'max'        => 'max',
        'min'        => 'min',
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->messageTemplates = $this->messageTemplates + [
                static::TOO_SHORT => __('Username is less than %min% characters long'),
                static::TOO_LONG  => __('Username is more than %max% characters long'),
            ];
        parent::__construct();
        $this->setConfigOption();
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

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
        $this->options = [
            'min'               => Pi::user()->config('uname_min'),
            'max'               => Pi::user()->config('uname_max'),
            'blacklist'         => Pi::user()->config('uname_blacklist'),
            'format'            => Pi::user()->config('uname_format'),
            'check_duplication' => true,
        ];

        return $this;
    }
}
