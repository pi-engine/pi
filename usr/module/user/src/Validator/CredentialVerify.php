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
