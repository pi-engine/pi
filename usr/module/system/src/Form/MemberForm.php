<?php
/**
 * Member account form
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
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\System\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Pi\Application\Db\User\RowGateway\Account;

class MemberForm extends BaseForm
{
    protected $user = array(
        'id'            => '',
        'identity'      => '',
        'name'          => '',
        'email'         => '',
        'active'        => '1',
        'role'          => 'member',
        'role_staff'    => '',
    );

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param Account $user User account data
     */
    public function __construct($name = null, $user = array())
    {
        $this->user = array_merge($this->user, $user);
        parent::__construct($name);
    }

    public function init()
    {
        $this->add(array(
            'name'          => 'identity',
            'options'       => array(
                'label' => __('User account'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'value' => $this->user['identity'],
            ),
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Display name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'value' => $this->user['name'],
            ),
        ));

        $this->add(array(
            'name'          => 'email',
            'options'       => array(
                'label' => __('Email address'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'value' => $this->user['email'],
            ),
        ));

        if (empty($this->user['id'])) {
            $this->add(array(
                'name'          => 'credential',
                'options'       => array(
                    'label' => __('New password'),
                ),
                'attributes'    => array(
                    'type'  => 'password',
                )
            ));

            $this->add(array(
                'name'          => 'credential-confirm',
                'options'       => array(
                    'label' => __('Confirm password'),
                ),
                'attributes'    => array(
                    'type'  => 'password',
                )
            ));
        }

        $this->add(array(
            'name'          => 'role',
            'type'          => 'role',
            'options'       => array(
                'label'     => __('User role'),
            ),
            'attributes'    => array(
                'value' => $this->user['role'],
            ),
        ));

        $this->add(array(
            'name'          => 'role_staff',
            'type'          => 'role',
            'options'       => array(
                'label'     => __('Management role'),
                'section'   => 'admin',
            ),
            'attributes'    => array(
                'value' => $this->user['role_staff'],
            ),
        ));

        $this->add(array(
            'name'          => 'active',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Active'),
            ),
            'attributes'    => array(
                'value' => $this->user['active'],
            ),
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => $this->user['id'],
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            ),
        ));
    }
}
