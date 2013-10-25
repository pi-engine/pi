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
 * Class for verifying and filtering form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class LoginFilter extends InputFilter
{
    public function __construct()
    {
        $config = Pi::service('registry')->config->read('user', 'account');

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

        if ($config['rememberme']) {
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