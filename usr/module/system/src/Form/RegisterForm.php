<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Register form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RegisterForm extends BaseForm
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $config = Pi::user()->config();

        $this->add([
            'name'       => 'identity',
            'options'    => [
            ],
            'attributes' => [
                'type'     => 'text',
                'required' => true,
            ],
        ]);

        $this->add([
            'name'       => 'name',
            'options'    => [
            ],
            'attributes' => [
                'type'     => 'text',
                'required' => true,
            ],
        ]);

        $this->add([
            'name'       => 'email',
            'options'    => [
            ],
            'attributes' => [
                'type'     => 'text',
                'required' => true,

            ],
        ]);

        $this->add([
            'name'       => 'credential',
            'options'    => [
            ],
            'attributes' => [
                'type'     => 'password',
                'required' => true,
            ],
        ]);

        $this->add([
            'name'       => 'credential-confirm',
            'options'    => [
            ],
            'attributes' => [
                'type'     => 'password',
                'required' => true,

            ],
        ]);

        if ($config['register_captcha']) {
            $this->add([
                'name'       => 'captcha',
                'type'       => 'captcha',
                'options'    => [
                    'separator'        => '<br />',
                    'captcha_position' => 'append',
                ],
                'attributes' => [
                    'required' => true,

                ],
            ]);
        }

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
