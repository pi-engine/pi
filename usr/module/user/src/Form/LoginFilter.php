<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Zend\InputFilter\InputFilter;

/**
 * Filter for user login
 */
class LoginFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'          => 'identity',
            'required'      => true,
            /*
            'filters'    => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            */
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

        /*
        $this->add(array(
            'name'      => 'field',
            'required'  => false,
        ));
        */

        $this->add(array(
            'name'      => 'rememberme',
            'required'  => false,
        ));

        $this->add(array(
            'name'      => 'redirect',
            'required'  => false,
        ));
    }
}