<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Laminas\InputFilter\InputFilter;

/**
 * Module form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleFilter extends InputFilter
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
                new \Module\System\Validator\ModuleName(),
            ],
        ]);

        $this->add([
            'name'       => 'title',
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                new \Module\System\Validator\ModuleTitle(),
            ],
        ]);

        $this->add([
            'name' => 'directory',
        ]);
    }
}
