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
 * Account form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AccountForm extends BaseForm
{
    /** @var Account User account model */
    protected $user;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param Account $user User account row
     */
    public function __construct($name = null, $user = null)
    {
        $this->user = $user;
        parent::__construct($name);
    }

    /**
     * Initialization
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
                'value' => $this->user->identity,
            ],
        ]);

        $this->add([
            'type'       => 'text',
            'name'       => 'name',
            'options'    => [
                'label' => __('Display name'),
            ],
            'attributes' => [
                'value' => $this->user->name,
            ],
        ]);

        $this->add([
            'type'       => 'date_select',
            'name'       => 'birthdate',
            'options'    => [
                'label' => __('Birthdate'),
            ],
            'attributes' => [
                'value' => $this->user->birthdate,
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
                'value' => $this->user->id,
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
