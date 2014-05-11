<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

class BlockCarouselForm extends BlockMediaForm
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
            'type'          =>  'Module\Widget\Form\Element\CarouselTemplate',
            'attributes'    => array(
                'required'  => true,
            )
        );
    }
}
