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
        parent::__construct();

        $this->messageTemplates = array(
            self::INVALID   => __('Invalid user name: %formatHint%'),
            self::RESERVED  => __('User name is reserved'),
            self::TAKEN     => __('User name is already taken'),
            self::TOO_SHORT => __('User name is less than %min% characters long'),
            self::TOO_LONG  => __('User name is more than %max% characters long')
        );
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
        $where = array('name' => $value);
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
        $this->options = array(
            'min'               => Pi::user()->config('name_min'),
            'max'               => Pi::user()->config('name_max'),
            'format'            => Pi::user()->config('name_format'),
            'backlist'          => Pi::user()->config('name_backlist'),
            'checkDuplication'  => true,
        );

        return $this;
    }

}