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
 * Navigation page form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavPageForm extends BaseForm
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add([
            'name'    => 'label',
            'options' => [
                'label' => __('Label'),
            ],
        ]);

        $this->add([
            'name'       => 'route',
            'options'    => [
                'label' => __('Route'),
            ],
            'attributes' => [
                'description' => __('Route to assemble URI.'),
            ],
        ]);

        $this->add([
            'name'    => 'module',
            'options' => [
                'label' => __('Module'),
            ],
        ]);

        $this->add([
            'name'    => 'controller',
            'options' => [
                'label' => __('Controller'),
            ],
        ]);

        $this->add([
            'name'    => 'action',
            'options' => [
                'label' => __('Action'),
            ],
        ]);

        $this->add([
            'name'    => 'uri',
            'options' => [
                'label' => __('URI'),
            ],
        ]);

        $this->add([
            'name'    => 'target',
            'type'    => 'select',
            'options' => [
                'label'         => __('Open target'),
                'value_options' => [
                    ''       => __('None'),
                    'self'   => __('Current window'),
                    '_blank' => __('Open a new window'),
                ],
            ],
        ]);

        $this->add([
            'name'       => 'resource',
            'options'    => [
                'label' => __('Permission resource'),
            ],
            'attributes' => [
                'description' =>
                    __('The resource identity for permission check.'),
            ],
        ]);

        $this->add([
            'name'       => 'visible',
            'type'       => 'checkbox',
            'options'    => [
                'label' => __('Display'),
            ],
            'attributes' => [
                'description' => __('To display in menu.'),
                'value'       => '1',
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'navigation',
            'attributes' => [
                'type' => 'hidden',
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
