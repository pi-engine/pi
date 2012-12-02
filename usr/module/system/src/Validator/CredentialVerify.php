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
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Validator
 * @version         $Id$
 */

namespace Module\System\Validator;

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
     * Crenditial validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $identity = $context['identity'];
        $credential = $value;

        $userRow = Pi::model('user')->find($identity, 'identity');
        if ($userRow->transformCredential($credential) != $userRow->getCredential()) {
            $this->error(static::INVALID);
            return false;
        }

        return true;
    }
}
