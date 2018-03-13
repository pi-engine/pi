<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi\Form\Form as BaseForm;

/**
 * Media edit form class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaEditForm extends BaseForm
{
    /**
     * Initalizing form
     */
    public function init()
    {
        $this->add([
            'name'       => 'name',
            'options'    => [
                'label' => __('Name'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('The unique identifier of media.'),
            ],
        ]);

        $this->add([
            'name'       => 'title',
            'options'    => [
                'label' => __('Title'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('Will be displayed on your website.'),
            ],

        ]);

        $this->add([
            'name'       => 'description',
            'options'    => [
                'label' => __('Description'),
            ],
            'attributes' => [
                'type'        => 'textarea',
                'description' => __('Display in the website depends on theme.'),
            ],

        ]);

        $this->add([
            'name'       => 'placeholder',
            'options'    => [
                'label' => __('Media'),
            ],
            'attributes' => [
                'type' => '',
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
            'name'       => 'fake_id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'url',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'type',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
            'type'       => 'submit',
        ]);
    }
}
