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
 * Category edit form class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class CategoryEditForm extends BaseForm
{
    /**
     * Initializing form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'parent',
            'options'    => array(
                'label'       => __('Parent'),
                'root'        => true,
            ),
            'attributes' => array(
                'description' => __('Category Hierarchy'),
            ),
            'type'       => 'Module\Article\Form\Element\Category',
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

        $this->add(array(
            'name'       => 'slug',
            'options'    => array(
                'label'       => __('Slug'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('The "Slug" is category name in URL.'),
            ),
        ));

        $this->add(array(
            'name'       => 'description',
            'options'    => array(
                'label'       => __('Description'),
            ),
            'attributes' => array(
                'type'        => 'textarea',
                'description' => __('Display in the website depends on theme.'),
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
        
        $module  = Pi::service('module')->current();
        $config  = Pi::config('', $module);
        $this->add(array(
            'name'       => 'image',
            'attributes' => array(
                
            ),
            'options'    => array(
                'preview'     => array(
                    'width'       => $config['category_width'],
                    'height'      => $config['category_height'],
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
                'name'       => 'category',
                'width'      => $config['category_width'],
                'height'     => $config['category_height'],
            )
        );
        $this->get('image')->setAjaxUrls($data);
        
        $this->add(array(
            'name'       => 'active',
            'options'    => array(
                'label'       => __('Active'),
            ),
            'attributes' => array(
                'value'       => 1,
            ),
            'type'       => 'checkbox',
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
