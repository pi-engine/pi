<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Form;

use Pi;
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
        $this->add(array(
            'name'          => 'cache_ttl',
            'type'          => 'cacheTtl',
            'options'       => array(
                'label' => __('Cache TTL'),
            ),
            /*
            'attributes'    => array(
                'type'  => 'select',
            )
            */
        ));

        $this->add(array(
            'name'          => 'cache_level',
            'type'          => 'cacheLevel',
            'options'       => array(
                'label' => __('Cache level'),
            ),
            /*
            'attributes'    => array(
                'type'  => 'select',
            )
            */
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}
