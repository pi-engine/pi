<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Laminas\InputFilter\InputFilter;

class TwoFactorFilter extends InputFilter
{
    public function __construct($option = [])
    {
        // verification
        $this->add(
            [
                'name'     => 'verification',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );

        // secret
        $this->add([
            'name'     => 'secret',
            'required' => true,
        ]);
    }
}