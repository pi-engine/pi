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
 * Validator user email
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserEmail extends SystemUserEmail
{
    public function __construct()
    {
        parent::__construct();

        $this->messageTemplates = array(
            self::RESERVED  => __('User email is reserved'),
            self::USED      => __('User email is already used'),
        );

        $this->setConfigOption();
    }

    /**
     * Set email validator according to config
     *
     * @return $this
     */
    public function setConfigOption()
    {
        $this->options = array(
            'backlist'         => Pi::user()->config('email_backlist'),
            'checkDuplication' => true,
        );

        return $this;
    }
}
