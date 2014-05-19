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
        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => _a('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'icon',
            'options'       => array(
                'label' => _a('Font-awesome icon'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'description'   => _a('Check http://fortawesome.github.io/Font-Awesome/icons/'),
            )
        ));

        $this->add(array(
            'name'          => 'order',
            'options'       => array(
                'label' => _a('Order'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name'          => 'op',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 'save',
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'class' => 'btn btn-primary',
            )
        ));
    }
}
