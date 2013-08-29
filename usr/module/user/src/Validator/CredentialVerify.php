<?php
/**
 * Credential verification validator
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

class CredentialVerify extends AbstractValidator
{
    const INVALID = 'credentialInvalid';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => 'The credential is not verified.',
    );

    /**
     * Set current module name
     *
     * @var string
     */
    protected $module = 'user';

    /**
     * Set user system account table name
     *
     * @var string
     */
    protected $tableName  = 'account';

    /**
     * Crenditial validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        return true;
    }
}
