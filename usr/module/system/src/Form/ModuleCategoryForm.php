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
 * Module category form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleCategoryForm extends BaseForm
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add([
            'name'       => 'title',
            'options'    => [
                'label' => _a('Title'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'icon',
            'options'    => [
                'label' => _a('Font-awesome icon'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => _a('Check http://fortawesome.github.io/Font-Awesome/icons/'),
            ],
        ]);

        $this->add([
            'name'       => 'order',
            'options'    => [
                'label' => _a('Order'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'op',
            'attributes' => [
                'type'  => 'hidden',
                'value' => 'save',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}
