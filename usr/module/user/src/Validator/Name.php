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

/**
 * Validator for user name
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Name extends Username
{
    public function __construct()
    {
        $this->messageTemplates = [
            static::INVALID   => __('Invalid name: %formatHint%'),
            static::RESERVED  => __('User name is reserved'),
            static::TAKEN     => __('User name is already taken'),
            static::TOO_SHORT => __('User name is less than %min% characters long'),
            static::TOO_LONG  => __('User name is more than %max% characters long'),
        ];
        parent::__construct();
        $this->setConfigOption();
    }

    /**
     * Check for duplication
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    protected function isDuplicated($value, $context)
    {
        $where = ['name' => $value];
        if (!empty($context['id'])) {
            $where['id <> ?'] = $context['id'];
        }
        $count = Pi::model('user_account')->count($where);
        if ($count) {
            return true;
        }

        return false;
    }

    /**
     * Set display validator according to config
     *
     * @return $this
     */
    public function setConfigOption()
    {
        $this->options = [
            'min'               => Pi::user()->config('name_min'),
            'max'               => Pi::user()->config('name_max'),
            'format'            => Pi::user()->config('name_format'),
            'blacklist'         => Pi::user()->config('name_blacklist'),
            'check_duplication' => true,
        ];

        return $this;
    }

}