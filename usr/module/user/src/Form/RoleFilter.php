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
 * Role form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add([
            'name'       => 'name',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                new \Module\System\Validator\RoleNameDuplicate(),
            ],

        ]);

        $this->add([
            'name'     => 'title',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        /*
        $this->add(array(
            'name'          => 'order',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'Int',
                ),
            ),
        ));
        */

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);

        $this->add([
            'name' => 'section',
        ]);
    }
}
