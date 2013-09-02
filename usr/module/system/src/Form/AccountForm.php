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
        $this->add(array(
            'type'          => 'text',
            'name'          => 'identity',
            'options'       => array(
                'label' => __('User account'),
            ),
            'attributes'    => array(
                'value' => $this->user->identity,
            ),
        ));

        $this->add(array(
            'type'          => 'text',
            'name'          => 'name',
            'options'       => array(
                'label' => __('Display name'),
            ),
            'attributes'    => array(
                'value' => $this->user->name,
            ),
        ));

        $this->add(array(
            'type'          => 'date_select',
            'name'          => 'birthdate',
            'options'       => array(
                'label' => __('Birthdate'),
            ),
            'attributes'    => array(
                'value' => $this->user->birthdate,
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
                'value' => $this->user->id,
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
