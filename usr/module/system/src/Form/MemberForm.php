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
use Pi\Form\Form as BaseForm;
use Pi\Application\Db\User\RowGateway\Account;

/**
 * Member form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class MemberForm extends BaseForm
{
    /**
     * User meta
     *
     * @var array
     */
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
     * @param null|string|int $name Optional name for the form
     * @param array $user User account data
     */
    public function __construct($name = null, $user = array())
    {
        $this->user = array_merge($this->user, $user);
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add(array(
            'type'          => 'text',
            'name'          => 'identity',
            'options'       => array(
                'label' => __('User account'),
            ),
            'attributes'    => array(
                'value' => $this->user['identity'],
            ),
        ));

        $this->add(array(
            'type'          => 'text',
            'name'          => 'name',
            'options'       => array(
                'label' => __('Display name'),
            ),
            'attributes'    => array(
                'value' => $this->user['name'],
            ),
        ));

        $this->add(array(
            'type'          => 'email',
            'name'          => 'email',
            'options'       => array(
                'label' => __('Email address'),
            ),
            'attributes'    => array(
                'value' => $this->user['email'],
            ),
        ));

        if (empty($this->user['id'])) {
            $this->add(array(
                'type'          => 'password',
                'name'          => 'credential',
                'options'       => array(
                    'label' => __('New password'),
                ),
            ));

            $this->add(array(
                'type'          => 'password',
                'name'          => 'credential-confirm',
                'options'       => array(
                    'label' => __('Confirm password'),
                ),
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
            'type'  => 'hidden',
            'name'  => 'id',
            'attributes'    => array(
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
