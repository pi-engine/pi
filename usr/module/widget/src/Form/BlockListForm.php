<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

/**
 * Form class for block has list content
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class BlockListForm extends BlockMediaForm
{
    /**
     * {@inheritDoc}
     */
    protected function getTemplateElement()
    {
        return array(
            'name'          => 'template',
            'options'       => array(
                'label' =>  _a('Template'),
            ),
            'type'          =>  'Module\Widget\Form\Element\ListTemplate',
            'attributes'    => array(
                'required'  => true,
            )
        );
    }
}
