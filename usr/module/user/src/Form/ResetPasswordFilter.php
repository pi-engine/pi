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
use Zend\InputFilter\InputFilter;

//use Module\User\Validator\CredentialVerify;

/**
 * Class for verifying and filtering form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ResetPasswordFilter extends InputFilter
{
    public function __construct($type = null)
    {
        $this->add([
            'name'       => 'credential-new',
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

        $this->add([
            'name'       => 'credential-confirm',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'Identical',
                    'options' => [
                        'token'  => 'credential-new',
                        'strict' => true,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name'     => 'token',
            'required' => true,
        ]);
    }
}
