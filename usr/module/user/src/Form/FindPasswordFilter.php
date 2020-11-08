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
class FindPasswordFilter extends InputFilter
{
    public function __construct()
    {
        // Check is mobile
        if (Pi::user()->config('is_mobile')) {
            $this->add(
                [
                    'name'     => 'identity',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        } else {
            $this->add(
                [
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
                    ],
                ]
            );
        }
    }
}