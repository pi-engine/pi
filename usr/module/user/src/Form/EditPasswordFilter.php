<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <hossein@azizabadi.com>
 */

namespace Module\User\Form;

use Zend\InputFilter\InputFilter;
use Module\System\Validator\UserEmail as UserEmailValidator;

class EditPasswordFilter extends InputFilter
{
    public function __construct($option = [])
    {
        $this->add(
            [
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
            ]
        );

        $this->add(
            [
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
            ]
        );
    }
}