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
 * Module category form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleCategoryFilter extends InputFilter
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
            'name'     => 'icon',
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'required' => false,
        ]);

        $this->add([
            'name'     => 'order',
            'filters'  => [
                [
                    'name' => 'Int',
                ],
            ],
            'required' => false,
        ]);

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);
    }
}
