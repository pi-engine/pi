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
use Module\System\Validator\UserEmail as SystemUserEmail;

/**
 * Validate user email
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserEmail extends SystemUserEmail
{
    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $options = $options ?: array();
        $options = array_merge(array(
            'blacklist'         => Pi::user()->config('email_blacklist'),
            'check_duplication' => true,
        ), $options);

        parent::__construct($options);
        $this->abstractOptions['messageTemplates'] = array(
            static::RESERVED    => __('User email is reserved'),
            static::USED        => __('User email is already used'),
        ) + $this->abstractOptions['messageTemplates'];
    }
}
