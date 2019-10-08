<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Form;

use Pi\Form\Form as BaseForm;

class RouteForm extends BaseForm
{
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new RouteFilter;
        }

        return $this->filter;
    }

    public function init()
    {
        $this->add([
            'name'       => 'name',
            'options'    => [
                'label' => __('Route name'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'type',
            'options'    => [
                'label' => __('Class'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'priority',
            'options'    => [
                'label' => __('Priority'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type'  => 'hidden',
                'value' => 0,
            ],
        ]);

        $this->add([
            'name'       => 'module',
            'attributes' => [
                'type'  => 'hidden',
                'value' => '',
            ],
        ]);

        $this->add([
            'name'       => 'section',
            'attributes' => [
                'type'  => 'hidden',
                'value' => 'front',
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
