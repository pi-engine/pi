<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Zend\InputFilter\InputFilter;
use Module\System\Validator\UserEmail as UserEmailValidator;
use Module\System\Validator\Username as UsernameValidator;

/**
 * Member form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class MemberFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $config = Pi::user()->config();

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
                new UsernameValidator(array(
                    'format'            => $config['uname_format'],
                    'blacklist'         => $config['uname_blacklist'],
                    'check_duplication' => true,
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
            'name'          => 'credential',
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
                        'token'     => 'credential',
                        'strict'    => true,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'email',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
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
                new UserEmailValidator(array(
                    'blacklist'         => $config['email_blacklist'],
                    'check_duplication' => true,
                )),
            ),
        ));

        $this->add(array(
            'name'          => 'role',
        ));

        $this->add(array(
            'name'          => 'role_staff',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'id',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'active',
        ));
    }
}
