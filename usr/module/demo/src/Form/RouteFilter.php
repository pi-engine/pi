<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Form;

use Laminas\InputFilter\InputFilter;

class RouteFilter extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name'       => 'name',
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                new \Module\Demo\Validator\RouteNameDuplicate(),
            ],
        ]);

        $this->add([
            'name'    => 'type',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name' => 'priority',

            'filters' => [
                [
                    'name' => 'Int',
                ],
            ],

        ]);

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'module',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'section',
            'required' => false,
        ]);
    }
}
