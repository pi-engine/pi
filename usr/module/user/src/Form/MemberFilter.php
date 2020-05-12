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

/**
 * Member form filter
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class MemberFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add([
            'name'       => 'identity',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [

            ],
        ]);

        $this->add([
            'name'       => 'name',
            'required'   => false,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name' => 'Module\User\Validator\Username',
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
                        'token'  => 'credential',
                        'strict' => true,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name'       => 'email',
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
            'name'     => 'front-role',
            'required' => true,
        ]);

        $this->add([
            'name'     => 'admin-role',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'activate',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'enable',
            'required' => false,
        ]);
    }
}
