<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Validator;

use Pi;
use Laminas\Validator\AbstractValidator;

class ReCaptcha extends AbstractValidator
{
    /**
     * {@inheritDoc}
     */
    public function isValid($value, $context = null)
    {
        if (isset($context['g-recaptcha-response']) && !empty($context['g-recaptcha-response'])) {
            $data = [
                'g-recaptcha-response' => $context['g-recaptcha-response']
            ];

            return Pi::service('form')->verifyReCaptcha($data);
        }

        return false;
    }
}
