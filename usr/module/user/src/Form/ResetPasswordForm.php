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
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of password
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ResetPasswordForm extends BaseForm
{
    protected $type;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param Account $user User account row
     */
    public function __construct($name = null, $type = null)
    {
        $this->type = $type;
        parent::__construct($name);
    }


    public function init()
    {
        $this->add(array(
            'name'          => 'credential-new',
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

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}
