<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

class BlockSpotlightForm extends BlockCarouselForm
{
    public function init()
    {
        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' =>  _a('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' =>  _a('Unique name'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'          => 'description',
            'options'       => array(
                'label' =>  _a('Description'),
            ),
            'attributes'    => array(
                'type'          => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'template',
            'options'       => array(
                'label' =>  _a('Template'),
            ),
            'type'          =>  'Module\Widget\Form\Element\SpotlightTemplate',
            'attributes'    => array(
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'id',
            'type'  => 'hidden',
        ));

        $this->add(array(
            'name'  => 'content',
            'type'  => 'hidden',
        ));

        $this->add(array(
            'name'  => 'title_hidden',
            'type'  => 'hidden',
            'attributes'    => array(
                'value' => '1',
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' =>  _a('Submit'),
            )
        ));
    }
}
