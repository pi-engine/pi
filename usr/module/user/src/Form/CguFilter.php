<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
namespace Module\User\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class CguFilter extends InputFilter
{
    public function __construct($option = array())
    {
        // id
        $this->add(array(
            'name' => 'id',
            'required' => false,
        ));
        // Version
        $this->add(array(
            'name' => 'version',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        // Filename
        $this->add(array(
            'name' => 'filename',
            'required' => false,
        ));

        // Active at
        $this->add(array(
            'name' => 'active_at',
            'required' => true,
        ));
    }
}