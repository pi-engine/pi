<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <hossein@azizabadi.com>
 */

namespace Module\User\Form;

use Pi\Form\Form as BaseForm;

class EditPasswordForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new EditPasswordFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        $this->add(
            [
                'name'       => 'credential-new',
                'options'    => [
                    'label' => __('New password'),
                ],
                'attributes' => [
                    'type' => 'password',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'credential-confirm',
                'options'    => [
                    'label' => __('Confirm password'),
                ],
                'attributes' => [
                    'type' => 'password',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'security',
                'type' => 'csrf',
            ]
        );

        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Submit'),
                ],
            ]
        );
    }
}