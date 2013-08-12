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
        $config = Pi::registry('config')->read('', 'user');

        $this->add(array(
            'name'          => 'identity',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'StringLength',
                    'options'   => array(
                        'encoding'  => 'UTF-8',
                        'min'       => $config['uname_min'],
                        'max'       => $config['uname_max'],
                    ),
                ),
                new \Module\System\Validator\UserName(array(
                    'format'            => $config['uname_format'],
                    'backlist'          => $config['uname_backlist'],
                    'checkDuplication'  => true,
                )),
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
            'name'          => 'id',
        ));
    }
}
