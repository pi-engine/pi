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
        $options = $options ?: [];
        $options = array_merge([
            'blacklist'         => Pi::user()->config('email_blacklist'),
            'check_duplication' => true,
        ], $options);

        parent::__construct($options);
        $this->abstractOptions['messageTemplates'] = [
                static::RESERVED => __('User email is reserved'),
                static::USED     => __('User email is already used'),
            ] + $this->abstractOptions['messageTemplates'];
    }
}
