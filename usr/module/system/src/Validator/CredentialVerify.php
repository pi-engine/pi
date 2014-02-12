<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Credential verification
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CredentialVerify extends AbstractValidator
{
    /** @var string */
    const INVALID = 'credentialInvalid';

    /**
     * Message templates
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
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $identity = $context['identity'];
        $credential = $value;

        $userRow = Pi::model('user')->find($identity, 'identity');
        if ($userRow->transformCredential($credential)
            != $userRow->getCredential()
        ) {
            $this->error(static::INVALID);
            return false;
        }

        return true;
    }
}
