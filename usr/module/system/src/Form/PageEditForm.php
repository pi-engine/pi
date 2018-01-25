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
 * Page edit form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageEditForm extends BaseForm
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add([
            'name'       => 'cache_type',
            'type'       => 'select',
            'options'    => [
                'label' => __('Cache type'),
            ],
            'attributes' => [
                'options' => [
                    'page'   => __('Page wide'),
                    'action' => __('Action data'),
                ],
                'value'   => 'page',
            ],
        ]);

        $this->add([
            'name' => 'cache_ttl',
            'type' => 'cache_ttl',
        ]);

        $this->add([
            'name' => 'cache_level',
            'type' => 'cache_level',
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
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}
