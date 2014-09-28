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
 * Cluster edit form class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class ClusterEditForm extends BaseForm
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
            ),
            'attributes' => array(
                'description' => __('Cluster Hierarchy'),
            ),
            'type'       => 'Module\Article\Form\Element\ClusterWithRoot',
            
        ));

        $this->add(array(
            'name'       => 'name',
            'options'    => array(
                'label'       => __('Name'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('The unique identifier of cluster.'),
            ),
            
        ));

        $this->add(array(
            'name'       => 'slug',
            'options'    => array(
                'label'       => __('Slug'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('The "Slug" is cluster name in URL.'),
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
                    'width'       => $config['cluster_width'],
                    'height'      => $config['cluster_height'],
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
                'action'     => 'save-image',
                'name'       => 'cluster',
                'width'      => $config['cluster_width'],
                'height'     => $config['cluster_height'],
            )
        );
        $this->get('image')->setAjaxUrls($data);
        
        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(                
                'value'       => __('Submit'),
            ),
            'type'       => 'submit',
        ));
    }
}
