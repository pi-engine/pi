<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi\Application\Db\User\RowGateway\Account;
use Pi\Form\Form as BaseForm;

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
    protected $user
        = [
            'id'         => '',
            'identity'   => '',
            'name'       => '',
            'email'      => '',
            'active'     => '1',
            'role'       => 'member',
            'role_staff' => '',
        ];

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the form
     * @param array $user User account data
     */
    public function __construct($name = null, $user = [])
    {
        $this->user = array_merge($this->user, $user);
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add([
            'type'       => 'text',
            'name'       => 'identity',
            'options'    => [
                'label' => __('User account'),
            ],
            'attributes' => [
                'value' => $this->user['identity'],
            ],
        ]);

        $this->add([
            'type'       => 'text',
            'name'       => 'name',
            'options'    => [
                'label' => __('Display name'),
            ],
            'attributes' => [
                'value' => $this->user['name'],
            ],
        ]);

        $this->add([
            'type'       => 'email',
            'name'       => 'email',
            'options'    => [
                'label' => __('Email address'),
            ],
            'attributes' => [
                'value' => $this->user['email'],
            ],
        ]);

        if (empty($this->user['id'])) {
            $this->add([
                'type'    => 'password',
                'name'    => 'credential',
                'options' => [
                    'label' => __('New password'),
                ],
            ]);

            $this->add([
                'type'    => 'password',
                'name'    => 'credential-confirm',
                'options' => [
                    'label' => __('Confirm password'),
                ],
            ]);
        }

        $this->add([
            'name'       => 'role',
            'type'       => 'role',
            'options'    => [
                'label' => __('User role'),
            ],
            'attributes' => [
                'value' => $this->user['role'],
            ],
        ]);

        $this->add([
            'name'       => 'role_staff',
            'type'       => 'role',
            'options'    => [
                'label'   => __('Management role'),
                'section' => 'admin',
            ],
            'attributes' => [
                'value' => $this->user['role_staff'],
            ],
        ]);

        $this->add([
            'name'       => 'active',
            'type'       => 'checkbox',
            'options'    => [
                'label' => __('Active'),
            ],
            'attributes' => [
                'value' => $this->user['active'],
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'type'       => 'hidden',
            'name'       => 'id',
            'attributes' => [
                'value' => $this->user['id'],
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}
