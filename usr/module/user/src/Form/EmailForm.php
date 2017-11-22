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
 * Class for initializing form of email
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EmailForm extends BaseForm
{
    public function init()
    {
        $this->add([
            'name'       => 'email-new',
            'options'    => [
                'label' => __('New email'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'credential',
            'options'    => [
                'label' => __('Current password'),
            ],
            'attributes' => [
                'type' => 'password',
            ],
        ]);

        $this->add([
            'name' => 'identity',
            'type' => 'hidden',
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
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