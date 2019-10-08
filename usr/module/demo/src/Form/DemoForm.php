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

class DemoForm extends BaseForm
{
    public function init()
    {
        $this->add([
            'name'       => 'date',
            'type'       => 'datepicker',
            'options'    => [
                'label'      => __('Date'),
                'datepicker' => [
                    'format'     => 'yyyy/mm/dd',
                    'start_Date' => '1984/09/04',
                    'End-date'   => '1984/12/03',
                ],
            ],
            'attributes' => [
                'id'    => 'demo-date',
                'value' => '1984/10/26',
            ],
        ]);

        $this->add([
            'name'    => 'content_text',
            'type'    => 'text',
            'options' => [
                'label' => __('Input'),
            ],
        ]);

        $this->add([
            'name'    => 'content_textarea',
            'type'    => 'textarea',
            'options' => [
                'label' => __('Text'),
            ],
        ]);

        $this->add([
            'name'       => 'description',
            'type'       => 'editor',
            'options'    => [
                'label'  => __('Description'),
                'editor' => 'html',
            ],
            'attributes' => [
                'placeholder' => __('Type your content'),
                'class'       => 'span6',
                'rows'        => 5,
            ],
        ]);

        $this->add([
            'name'       => 'content_html',
            'type'       => 'editor',
            'options'    => [
                'label'  => __('HTML'),
                'editor' => 'html',
            ],
            'attributes' => [
                'placeholder' => __('Type your content'),
                'class'       => 'span6',
                'rows'        => 5,
            ],
        ]);

        $this->add([
            'name'       => 'content_html_second',
            'type'       => 'editor',
            'options'    => [
                'label'  => __('HTML2'),
                'editor' => 'html',
            ],
            'attributes' => [
                'placeholder' => __('Type your content'),
                'class'       => 'span6',
                'rows'        => 5,
            ],
        ]);

        $this->add([
            'name'       => 'content_markdown',
            'type'       => 'editor',
            'options'    => [
                'label'  => __('Markdown'),
                'editor' => 'makeitup',
            ],
            'attributes' => [
                'placeholder' => __('Type your content'),
                'class'       => 'span6',
                'rows'        => 5,
            ],
        ]);

        $this->add([
            'name'    => 'upload_file',
            'type'    => 'file',
            'options' => [
                'label' => __('File'),
            ],
        ]);

        $this->add([
            'name'       => 'rename',
            'type'       => 'radio',
            'options'    => [
                'label'         => __('File naming'),
                'value_options' => [
                    'overwrite' => __('Keep name and overwrite when duplicated'),
                    'random'    => __('Rename to random'),
                ],
            ],
            'attributes' => [
                'value' => 'random',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Ready to go!'),
            ],
        ]);
    }
}
