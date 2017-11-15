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
        $this->add(array(
            'name'          => 'date',
            'type'          => 'datepicker',
            'options'   => array(
                'label'         => __('Date'),
                'datepicker'    => array(
                    'format'    => 'yyyy/mm/dd',
                    'start_Date' => '1984/09/04',
                    'End-date'   => '1984/12/03',
                ),
            ),
            'attributes'    => array(
                'id'    => 'demo-date',
                'value' => '1984/10/26',
            )
        ));

        $this->add(array(
            'name'          => 'content_text',
            'type'          => 'text',
            'options'   => array(
                'label' => __('Input'),
            ),
        ));

        $this->add(array(
            'name'          => 'content_textarea',
            'type'          => 'textarea',
            'options'   => array(
                'label' => __('Text'),
            ),
        ));

        $this->add(array(
            'name'          => 'description',
            'type'          => 'editor',
            'options'       => array(
                'label'     => __('Description'),
                'editor'    => 'html',
            ),
            'attributes'    => array(
                'placeholder'   => __('Type your content'),
                'class'         => 'span6',
                'rows'          => 5,
            ),
        ));

        $this->add(array(
            'name'          => 'content_html',
            'type'          => 'editor',
            'options'       => array(
                'label'     => __('HTML'),
                'editor'    => 'html',
            ),
            'attributes'    => array(
                'placeholder'   => __('Type your content'),
                'class'         => 'span6',
                'rows'          => 5,
            ),
        ));

        $this->add(array(
            'name'          => 'content_html_second',
            'type'          => 'editor',
            'options'       => array(
                'label'     => __('HTML2'),
                'editor'    => 'html',
            ),
            'attributes'    => array(
                'placeholder'   => __('Type your content'),
                'class'         => 'span6',
                'rows'          => 5,
            ),
        ));

        $this->add(array(
            'name'          => 'content_markdown',
            'type'          => 'editor',
            'options'       => array(
                'label'     => __('Markdown'),
                'editor'    => 'makeitup',
            ),
            'attributes'    => array(
                'placeholder'   => __('Type your content'),
                'class'         => 'span6',
                'rows'          => 5,
            ),
        ));

        $this->add(array(
            'name'          => 'upload_file',
            'type'          => 'file',
            'options'   => array(
                'label' => __('File'),
            ),
        ));

        $this->add(array(
            'name'      => 'rename',
            'type'      => 'radio',
            'options'   => array(
                'label' => __('File naming'),
                'value_options'   => array(
                    'overwrite' => __('Keep name and overwrite when duplicated'),
                    'random'    => __('Rename to random'),
                ),
            ),
            'attributes'    => array(
                'value' => 'random',
            )
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Ready to go!'),
            )
        ));
    }
}
