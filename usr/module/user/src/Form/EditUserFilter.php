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
 * Class for verifying and filtering form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EditUserFilter extends InputFilter
{
    public function __construct($filters)
    {
        /*
        $customVerifyFields = array(
            'email',
            'identity',
            'name'
        );
        */
        foreach ($filters as $filter) {
            switch ($filter['name']) {
                case 'credential':
                    $this->add(
                        [
                            'name'       => 'credential',
                            'required'   => false,
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
                    break;

                case 'identity':
                    $this->add(
                        [
                            'name'       => 'identity',
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
                        ]
                    );
                    break;

                case 'email':
                    $this->add(
                        [
                            'name'       => 'email',
                            'required'   => false,
                            'filters'    => [
                                [
                                    'name' => 'StringTrim',
                                ],
                            ],
                            'validators' => [
                                [
                                    'name' => 'Module\User\Validator\UserEmail',
                                ],
                            ],
                        ]
                    );
                    break;

                case 'name':
                    $this->add(
                        [
                            'name'       => 'name',
                            'required'   => false,
                            'filters'    => [
                                [
                                    'name' => 'StringTrim',
                                ],
                            ],
                            'validators' => [
                                [
                                    'name' => 'Module\User\Validator\Name',
                                ],
                            ],
                        ]
                    );
                    break;

                default:
                    $this->add($filter);
                    break;
            }
        }
    }
}