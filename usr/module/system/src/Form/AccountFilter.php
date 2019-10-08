<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Module\System\Validator\Username as UsernameValidator;
use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Account form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AccountFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $config = Pi::user()->config();

        $this->add([
            'name'       => 'identity',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => $config['uname_min'],
                        'max'      => $config['uname_max'],
                    ],
                ],
                new UsernameValidator([
                    'format'            => $config['uname_format'],
                    'blacklist'         => $config['uname_blacklist'],
                    'check_duplication' => true,
                ]),
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
            'name' => 'id',
        ]);
    }
}
