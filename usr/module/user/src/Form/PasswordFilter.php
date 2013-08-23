<?php
/**
 * User system password form input filter
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefoucs.com>
 * @since           3.0
 * @package         Module\User
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\User\Form;

use Pi;
use Zend\InputFilter\InputFilter;
use Module\User\Validator\CredentialVerify;

class PasswordFilter extends InputFilter
{
    public function __construct($type = null)
    {
        $config = Pi::service('registry')->config->read('user', 'general');

        if (!$type) {
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
        }

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
    }
}
