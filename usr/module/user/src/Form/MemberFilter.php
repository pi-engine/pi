<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Member form filter
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class MemberFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add(array(
            'name'          => 'identity',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(

            ),
        ));

        $this->add(array(
            'name'          => 'name',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name' => 'Module\User\Validator\Username',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'credential',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name' => 'Module\User\Validator\Password',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'credential-confirm',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'Identical',
                    'options'   => array(
                        'token'     => 'credential',
                        'strict'    => true,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'email',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'EmailAddress',
                    'options'   => array(
                        'useMxCheck'        => false,
                        'useDeepMxCheck'    => false,
                        'useDomainCheck'    => false,
                    ),
                ),
                array(
                    'name' => 'Module\User\Validator\UserEmail',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'front-role',
            'required'      => true,
        ));

        $this->add(array(
            'name'          => 'admin-role',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'activate',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'enable',
            'required'      => false,
        ));
    }
}
