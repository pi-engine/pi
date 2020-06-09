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
use Laminas\Validator\AbstractValidator;

/**
 * User credential verification
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
    protected $messageTemplates = [];

    public function __construct()
    {
        $this->messageTemplates = [
            self::INVALID => __('Invalid password.'),
        ];

        parent::__construct();
    }

    /**
     * Credential validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $identity   = $context['identity'];
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
