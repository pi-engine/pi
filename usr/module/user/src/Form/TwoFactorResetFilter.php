<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

use Laminas\InputFilter\InputFilter;

/**
 * Class for verifying and filtering form
 *
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */
class TwoFactorResetFilter extends InputFilter
{
    public function __construct($type = null)
    {
        // reset_two_factor
        $this->add(
            [
                'name'     => 'reset_two_factor',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );
    }
}
