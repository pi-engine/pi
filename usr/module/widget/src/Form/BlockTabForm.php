<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

class BlockTabForm extends AbstractBaseForm
{
    /**
     * {@inheritDoc}
     */
    protected function getTemplateElement()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTemplateFilter()
    {
        return false;
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
