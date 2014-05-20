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
 * Class for verifying and filtering form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class SearchFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add(array(
            'name'          => 'active',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'enable',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'activated',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'front-role',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'admin-role',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'identity',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
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
        ));

        $this->add(array(
            'name'          => 'email',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'time-created-from',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'time-created-end',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'ip-register',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));
    }
}
