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

/**
 * Class for verifying and filtering form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class SearchFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add([
            'name'     => 'active',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'enable',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'activated',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'front-role',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'admin-role',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'identity',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
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
            'name'     => 'email',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'     => 'time-created-from',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'time-created-end',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'ip-register',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);
    }
}
