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
use Module\System\Validator\CredentialVerify;

/**
 * Password change form fitler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PasswordFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $config = Pi::registry('config')->read('', 'user');

        $this->add(array(
            'name'          => 'credential',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new CredentialVerify(),
            ),
        ));

        $this->add(array(
            'name'          => 'credential-new',
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
                        'min'       => $config['password_min'],
                        'max'       => $config['password_max'],
                    ),
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
                        'token'     => 'credential-new',
                        'strict'    => true,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'identity'
        ));
    }
}
