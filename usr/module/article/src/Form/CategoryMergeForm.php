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
 * Category merge form class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CategoryMergeForm extends BaseForm
{
    /**
     * Initializing form
     */
    public function init()
    {
        $this->add([
            'name'       => 'from',
            'options'    => [
                'label' => __('From'),
            ],
            'attributes' => [
                'id'    => 'from',
                'class' => 'form-control',
            ],
            'type'       => 'Module\Article\Form\Element\Category',
        ]);

        $this->add([
            'name'       => 'to',
            'options'    => [
                'label' => __('To'),
            ],
            'attributes' => [
                'id'    => 'to',
                'class' => 'form-control',
            ],
            'type'       => 'Module\Article\Form\Element\Category',
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
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
