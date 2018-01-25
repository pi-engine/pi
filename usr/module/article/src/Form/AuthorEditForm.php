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
 * Class for initializing form of add author page
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AuthorEditForm extends BaseForm
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
                'id'   => 'name',
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'placeholder',
            'options'    => [
                'label' => __('Photo'),
            ],
            'attributes' => [
            ],
        ]);

        $this->add([
            'name'       => 'description',
            'options'    => [
                'label' => __('Biography'),
            ],
            'attributes' => [
                'id'   => 'bio',
                'type' => 'textarea',
            ],
        ]);

        $this->add([
            'name'       => 'photo',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'id'   => 'id',
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'fake_id',
            'attributes' => [
                'id'   => 'fake_id',
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
