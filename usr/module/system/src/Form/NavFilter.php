<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Zend\InputFilter\InputFilter;

/**
 * Navigation form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavFilter extends InputFilter
{
    /**
     * Constructor
     */
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
                [
                    'name'    => 'Regex',
                    'options' => [
                        'pattern' => '/[a-z0-9_]/',
                    ],
                ],
                new \Module\System\Validator\NavNameDuplicate(),
            ],
        ]);

        $this->add([
            'name'    => 'title',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name' => 'section',
        ]);

        /*
        $this->add(array(
            'name'          => 'active',
        ));
        */

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'parent',
            'required' => false,
        ]);
    }
}
