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
 * Navigation form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavForm extends BaseForm
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add([
            'name'       => 'name',
            'options'    => [
                'label' => __('Unique name'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'title',
            'options'    => [
                'label' => __('Title'),
            ],
            'attributes' => [
                'type' => 'text',
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
            'name'       => 'id',
            'attributes' => [
                'type'  => 'hidden',
                'value' => '',
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
