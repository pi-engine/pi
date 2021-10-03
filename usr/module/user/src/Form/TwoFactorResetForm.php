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
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class TwoFactorResetForm extends BaseForm
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
        // reset_two_factor
        $this->add(
            [
                'name'       => 'reset_two_factor',
                'options'    => [
                    'label'         => __('Reset Two-Factor'),
                    'value_options' => [
                        1 => __('Yes'),
                        0 => __('No'),
                    ],
                ],
                'type'       => 'radio',
                'attributes' => [
                    'description' => __('After resetting, the user must remove two-factor settings on their mobile phone, after that scan and setup two-factor settings again'),
                    'value'    => 1,
                    'required' => true,
                ],
            ]
        );

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}
