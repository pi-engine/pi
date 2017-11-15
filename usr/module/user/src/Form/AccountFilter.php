<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Class for verifying and filtering form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class AccountFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'       => 'email',
            'require'    => true,
            'filters'    => array(
                array(
                    'name' => 'StringTrim',
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
                    'name'      => 'Module\User\Validator\UserEmail',
                ),
            ),
        ));

        $this->add(array(
            'name'       => 'name',
            'require'    => true,
            'filters'    => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators' => array(
                array(
                    'name'      => 'Module\User\Validator\Name',
                ),
            ),
        ));
    }
}