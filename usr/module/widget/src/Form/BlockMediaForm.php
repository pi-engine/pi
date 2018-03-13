<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        $spec    = array_replace($tplSpec, [
            'name'       => 'template',
            'options'    => [
                'label' => _a('Template'),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        return $spec;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTemplateFilter()
    {
        return [
            'name'    => 'template',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ];
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
