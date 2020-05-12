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
 * Login form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoginFilter extends InputFilter
{
    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->add([
            'name'     => 'identity',
            'required' => true,
        ]);

        $this->add([
            'name'     => 'credential',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        if (!empty($config['rememberme'])) {
            $this->add([
                'name'     => 'rememberme',
                'required' => false,
            ]);
        }

        $this->add([
            'name'     => 'redirect',
            'required' => false,
        ]);
    }
}