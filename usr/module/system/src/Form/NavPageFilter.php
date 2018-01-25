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
 * Navigation page form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavPageFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add([
            'name'    => 'title',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'    => 'route',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'    => 'module',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'    => 'controller',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'    => 'action',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'    => 'uri',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
                [
                    'name' => 'Pi\Filter\Uri',
                ],
            ],
        ]);

        $this->add([
            'name' => 'active',
        ]);

        $this->add([
            'name' => 'target',
        ]);

        $this->add([
            'name' => 'id',
        ]);

        $this->add([
            'name'     => 'navigation',
            'required' => true,
        ]);
    }
}
