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
 * Role form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add(array(
            'name'          => 'name',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new \Module\System\Validator\RoleNameDuplicate(),
            ),

        ));

        $this->add(array(
            'name'          => 'title',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        /*
        $this->add(array(
            'name'          => 'order',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'Int',
                ),
            ),
        ));
        */

        $this->add(array(
            'name'          => 'id',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'section',
        ));
    }
}
