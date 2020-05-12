<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Module\System\Validator;
use Laminas\InputFilter\InputFilter;

/**
 * Page adding form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageAddFilter extends InputFilter
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
            'name'       => 'controller',
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                new Validator\ControllerAvailable(),
                new Validator\PageDuplicate(),
            ],
        ]);

        $this->add([
            'name'       => 'action',
            'required'   => false,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                new Validator\ActionAvailable(),
            ],
        ]);

        $this->add([
            'name'     => 'section',
            'required' => true,
        ]);

        $this->add([
            'name'     => 'module',
            'required' => true,
        ]);

        /*
        $this->add(array(
            'name'      => 'cache_type',
            'required'  => false,
        ));

        $this->add(array(
            'name'      => 'cache_ttl',
            'required'  => false,
        ));

        $this->add(array(
            'name'      => 'cache_level',
            'required'  => false,
        ));
        */
    }
}
