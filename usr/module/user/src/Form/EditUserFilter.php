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
class EditUserFilter extends InputFilter
{
    public function __construct($filters, $uid = null)
    {
        $customVerifyFields = array(
            'email',
            'identity',
            'name'
        );
        foreach ($filters as $filter) {
            if ($filter['name'] == 'credential') {
                $config = Pi::service('registry')->config->read('user', 'general');
                $this->add(array(
                    'name'          => 'credential',
                    'required'      => false,
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
            }
        }

    }
}