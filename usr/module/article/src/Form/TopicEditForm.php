<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
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
        $this->add(array(
            'name'       => 'slug',
            'options'    => array(
                'label'       => __('Slug'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('The "Slug" is topic name in URL.'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'title',
            'options'    => array(
                'label'       => __('Title'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Will be displayed on your website.'),
            ),
        ));
        
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
        $this->add(array(
            'name'       => 'content',
            'options'    => array(
                'label'       => __('Content'),
                'editor'      => $editor,
                'set'         => $set,
            ),
            'attributes' => array(
                'id'          => 'content',
                'type'        => 'editor',
                'description' => __('Topic main content.'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'description',
            'options'    => array(
                'label'       => __('Description'),
            ),
            'attributes' => array(
                'type'        => 'textarea',
                'description' => __('Display in the website.'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'image',
            'attributes' => array(
                
            ),
            'options'    => array(
                'preview'     => array(
                    'width'       => $config['topic_width'],
                    'height'      => $config['topic_height'],
                ),
                'type'        => 'image',
                'to_session'  => true,
            ),
            'type'        => 'Module\Article\Form\Element\FeatureImage',
        ));
        $data['custom_save'] = Pi::service('url')->assemble(
            '', 
            array(
                'controller' => 'ajax',
                'action'     => 'save-media',
                'name'       => 'topic',
                'width'      => $config['topic_width'],
                'height'     => $config['topic_height'],
            )
        );
        $this->get('image')->setAjaxUrls($data);
        
        $this->add(array(
            'name'       => 'template-placeholder',
            'options'    => array(
                'label'       => __('Template'),
            ),
            'attributes' => array(
                'description' => __('Choose a template for topic.'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'security',
            'type'       => 'csrf',
        ));

        $this->add(array(
            'name'       => 'id',
            'attributes' => array(
                'type'        => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name'       => 'template',
            'attributes' => array(
                'type'        => 'hidden',
                'value'       => 'default',
            ),
        ));
        
        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(                
                'value'       => __('Submit'),
            ),
            'type'       => 'submit',
        ));
    }
}
