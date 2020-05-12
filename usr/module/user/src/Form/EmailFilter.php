<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Laminas\InputFilter\InputFilter;

//use Module\User\Validator\CredentialVerify;

/**
 * Class for verifying and filtering form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EmailFilter extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name'       => 'email-new',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'EmailAddress',
                    'options' => [
                        'useMxCheck'     => false,
                        'useDeepMxCheck' => false,
                        'useDomainCheck' => false,
                    ],
                ],
                [
                    'name' => 'Module\User\Validator\UserEmail',
                ],
            ],
        ]);

        $this->add([
            'name'       => 'credential',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name' => 'Module\User\Validator\Password',
                ],
            ],
        ]);
    }
}