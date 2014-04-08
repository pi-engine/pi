<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Zend\InputFilter\InputFilter;

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
        $this->add(array(
            'name'          => 'identity',
            'required'      => true,
        ));

        $this->add(array(
            'name'          => 'credential',
            'required'      => true,
            'filters'    => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        if (!empty($config['rememberme'])) {
            $this->add(array(
                'name'      => 'rememberme',
                'required'  => false,
            ));
        }

        $this->add(array(
            'name'      => 'redirect',
            'required'  => false,
        ));
    }
}