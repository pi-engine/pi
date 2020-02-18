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
     * @param string|int $name Optional name for the element
     * @param string $type
     */
    public function __construct($name = null, $type = null)
    {
        $this->type = $type;
        parent::__construct($name);
    }


    public function init()
    {
        $this->add([
            'name'       => 'credential-new',
            'options'    => [
                'label' => __('New password'),
            ],
            'attributes' => [
                'type' => 'password',
            ],
        ]);

        $this->add([
            'name'       => 'credential-confirm',
            'options'    => [
                'label' => __('Confirm password'),
            ],
            'attributes' => [
                'type' => 'password',
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name' => 'token',
            'type' => 'hidden',
        ]);

        $this->add([
            'name' => 'redirect',
            'type' => 'hidden',
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
