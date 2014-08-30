<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
            'name'          => 'cache_type',
            'type'          => 'select',
            'options'       => array(
                'label' => __('Cache type'),
            ),
            'attributes'    => array(
                'options'   => array(
                    'page'      => __('Page wide'),
                    'action'    => __('Action data'),
                ),
                'value'     => 'page',
            ),
        ));

        $this->add(array(
            'name'          => 'cache_ttl',
            'type'          => 'cache_ttl',
        ));

        $this->add(array(
            'name'          => 'cache_level',
            'type'          => 'cache_level',
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
