<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Validator;

use Module\System\Validator\CredentialVerify as SystemCredentialVerify;

/**
 * User credential verification
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CredentialVerify extends SystemCredentialVerify
{
    public function __construct()
    {
        $this->messageTemplates = array(
            self::INVALID => __('Invalid password.'),
        );

        parent::__construct();
    }
}
