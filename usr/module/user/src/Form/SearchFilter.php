<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Search form filter
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
            'name'          => 'state',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'enable',
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
            'name'          => 'time-login-from',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'time-login-to',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'avatar',
            'required'      => false,
        ));
    }
}
