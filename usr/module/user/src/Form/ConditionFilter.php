<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */

namespace Module\User\Form;

use Laminas\InputFilter\InputFilter;

class ConditionFilter extends InputFilter
{
    public function __construct($option = [])
    {
        // id
        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);
        // Version
        $this->add([
            'name'       => 'version',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'Regex',
                    'options' => [
                        'pattern' => '/[0-9*].[0-9*]/',
                    ],
                ],
            ],
        ]);
        // Filename
        $this->add([
            'name'     => 'filename',
            'required' => false,
        ]);

        // Active at
        $this->add([
            'name'     => 'active_at',
            'required' => true,
        ]);
    }
}
