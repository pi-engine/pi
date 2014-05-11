<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

//use Pi\Form\Form as BaseForm;
//use Zend\InputFilter\InputFilter;
//use Module\Widget\Validator\WidgetNameDuplicate;

class BlockMediaForm extends AbstractBaseForm
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
            'attributes'    => array(
                'required'  => true,
                'value'     => 'media-list',
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getTemplateFilter()
    {
        return array(
            'name'          => 'template',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getContentElement()
    {
        return array(
            'name'  => 'content',
            'type'  => 'hidden',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getContentFilter()
    {
        return array(
            'name'          => 'content',
            'required'      => true,
            'allow_empty'   => true,
        );
    }
}
