<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of two factor
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class TwoFactorForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new TwoFactorFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // verification
        $this->add(
            [
                'name'       => 'verification',
                'options'    => [
                    'label' => __('Two-factor authentication code'),
                ],
                'attributes' => [
                    'type'         => 'text',
                    'description'  => __("Enter the code from the two-factor app on your mobile device. If you've lost your device, you may enter one of your recovery codes."),
                    'required'     => true,
                    'autocomplete' => 'off',
                ],
            ]
        );

        // secret
        $this->add([
            'name' => 'secret',
            'type' => 'hidden',
        ]);

        // security
        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        // Submit
        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Verify code'),
            ],
        ]);
    }
}