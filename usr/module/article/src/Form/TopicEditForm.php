<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Topic edit form
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class TopicEditForm extends BaseForm
{
    /**
     * Initializing form
     */
    public function init()
    {
        $this->add([
            'name'       => 'name',
            'options'    => [
                'label' => __('Name'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('The unique identifier of category.'),
            ],
        ]);

        $this->add([
            'name'       => 'slug',
            'options'    => [
                'label' => __('Slug'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('The "Slug" is topic name in URL.'),
            ],
        ]);

        $this->add([
            'name'       => 'title',
            'options'    => [
                'label' => __('Title'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('Will be displayed on your website.'),
            ],
        ]);

        $module = Pi::service('module')->current();
        $config = Pi::config('', $module);
        switch ($config['markup']) {
            case 'html':
                $editor = 'html';
                $set    = '';
                break;
            case 'compound':
                $editor = 'markitup';
                $set    = 'html';
                break;
            case 'markdown':
                $editor = 'markitup';
                $set    = 'markdown';
                break;
            default:
                $editor = 'textarea';
                $set    = '';
        }
        $this->add([
            'name'       => 'content',
            'options'    => [
                'label'  => __('Content'),
                'editor' => $editor,
                'set'    => $set,
            ],
            'attributes' => [
                'id'          => 'content',
                'type'        => 'editor',
                'description' => __('Topic main content.'),
            ],
        ]);

        $this->add([
            'name'       => 'placeholder',
            'options'    => [
                'label' => __('Image'),
            ],
            'attributes' => [
                'type'        => '',
                'description' => __('Topic feature image, optional.'),
            ],
        ]);

        $this->add([
            'name'       => 'description',
            'options'    => [
                'label' => __('Description'),
            ],
            'attributes' => [
                'type'        => 'textarea',
                'description' => __('Display in the website.'),
            ],
        ]);

        $this->add([
            'name'       => 'template-placeholder',
            'options'    => [
                'label' => __('Template'),
            ],
            'attributes' => [
                'description' => __('Choose a template for topic.'),
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'fake_id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'image',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'template',
            'attributes' => [
                'type'  => 'hidden',
                'value' => 'default',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
            'type'       => 'submit',
        ]);
    }
}
