<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

class BlockStaticForm extends AbstractBaseForm
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
        $set = '';
        switch ($this->contentType) {
            case 'html':
                $editor = 'html';
                break;
            case 'markdown':
                $editor = 'markitup';
                $set    = 'markdown';
                break;
            case 'text':
            default:
                $editor = 'textarea';
                break;
        }

        return [
            'name'       => 'content',
            'options'    => [
                'label'  => _a('Content'),
                'editor' => $editor,
                'set'    => $set,
            ],
            'type'       => 'editor',
            'attributes' => [
                'class'       => 'span6',
                'description' => _a('Tags supported: `%sitename%` - site name; `%siteurl%` - site root URL; `%slogan%` - site slogan'),
            ],
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
            'allow_empty' => false,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getTypeElement()
    {
        return [
            'name'       => 'type',
            'options'    => [
                'label' => _a('Content type'),
            ],
            'attributes' => [
                'readonly' => true,
                'value'    => $this->contentType,
            ],
        ];
    }
}
