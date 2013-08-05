<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
     */
    public function __construct()
    {
        $this->add(array(
            'name'          => 'identity',
            'required'      => true,
            'filters'    => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
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

        if (Pi::config('rememberme', 'user')) {
            $this->add(array(
                'name'  => 'rememberme',
            ));
        }

        $this->add(array(
            'name'      => 'redirect',
            'required'  => false,
        ));
    }
}
