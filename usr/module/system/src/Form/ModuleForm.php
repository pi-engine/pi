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
 * Module form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleForm extends BaseForm
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Unique name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => __('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'directory',
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
