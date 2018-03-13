<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        return [
            'name' => 'content',
            'type' => 'hidden',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getContentFilter()
    {
        return [
            'name'        => 'content',
            'required'    => true,
            'allow_empty' => true,
        ];
    }
}
