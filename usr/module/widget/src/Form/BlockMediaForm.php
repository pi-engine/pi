<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

use Pi;

class BlockMediaForm extends AbstractBaseForm
{
    /**
     * {@inheritDoc}
     */
    protected function getTemplateElement()
    {
        $tplSpec = Pi::api('block', 'widget')->templateSpec($this->contentType);
        $spec = array_replace($tplSpec, array(
            'name'          => 'template',
            'options'       => array(
                'label' =>  _a('Template'),
            ),
            'attributes'    => array(
                'required'  => true,
            )
        ));

        return $spec;
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
