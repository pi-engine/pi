<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Module\System\Validator\UserEmail as UserEmailValidator;
use Module\System\Validator\Username as UsernameValidator;
use Pi;
use Laminas\InputFilter\InputFilter;

/**
 * Register form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RegisterFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $config = Pi::user()->config();

        $this->add([
            'name'       => 'identity',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => $config['uname_min'],
                        'max'      => $config['uname_max'],
                    ],
                ],
                new UsernameValidator([
                    'format'            => $config['uname_format'],
                    'blacklist'         => $config['uname_blacklist'],
                    'check_duplication' => true,
                ]),
            ],
        ]);

        $this->add([
            'name'     => 'name',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
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
                new UserEmailValidator([
                    'blacklist'         => $config['email_blacklist'],
                    'check_duplication' => true,
                ]),
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
                    'name'    => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => $config['password_min'],
                        'max'      => $config['password_max'],
                    ],
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
    }
}
