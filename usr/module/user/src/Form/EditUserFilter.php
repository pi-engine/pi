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
            if ($filter['name'] == 'credential') {
                $this->add([
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
                ]);
            } else {
                $this->add($filter);
            }
        }

    }
}