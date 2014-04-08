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
use Module\Article\Controller\Admin\SetupController as Config;

/**
 * Draft edit form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftEditForm extends BaseForm
{
    /**
     * The mode of displaying for elements
     * @var string 
     */
    protected $mode = Config::FORM_MODE_EXTENDED;
    
    /**
     * Elements to display
     * @var array 
     */
    protected $items = array();
    
    /**
     * Initialize object
     * 
     * @param string  $name     Form name
     * @param array   $options  Optional parameters
     */
    public function __construct($name, $options = array())
    {
        if (isset($options['mode'])) {
            $this->mode = $options['mode'];
        }
        if (Config::FORM_MODE_CUSTOM == $this->mode) {
            $this->items = isset($options['elements']) 
                ? $options['elements'] : array();
        } elseif (!empty($options['elements'])) {
            $this->items = $options['elements'];
        } else {
            $this->items = $this->getDefaultElements($this->mode);
        }
        parent::__construct($name);
    }
    
    /**
     * Get defined form element
     * !!! The value of each field must be the name of each form
     * 
     * @return array 
     */
    public static function getExistsFormElements()
    {
        return array(
            'subject'         => __('Subject'),
            'subtitle'        => __('Subtitle'),
            'summary'         => __('Summary'),
            'content'         => __('Content'),
            'image'           => __('Image'),
            'author'          => __('Author'),
            'source'          => __('Source'),
            'category'        => __('Category'),
            'tag'             => __('Tag'),
            'related'         => __('Related'),
            'slug'            => __('Slug'),
            'seo_title'       => __('SEO Title'),
            'seo_keywords'    => __('SEO Keywords'),
            'seo_description' => __('SEO Description'),
        );
    }
    
    public static function getNeededElements()
    {
        return array('subject', 'content', 'category');
    }

    /**
     * Get default elements for displaying
     *
     * @param string $mode
     *
     * @return array
     */
    public static function getDefaultElements(
        $mode = Config::FORM_MODE_EXTENDED
    ) {
        $normal = array(
            'subject',
            'subtitle',
            'summary',
            'content',
            'image',
            'author',
            'source',
            'category',
            'tag',
        );
        
        $extended = array_merge($normal, array(
            'related',
            'slug',
            'seo_title',
            'seo_keywords',
            'seo_description',
        ));
        
        return (Config::FORM_MODE_NORMAL == $mode) ? $normal : $extended;
    }
    
    /**
     * Initialize form element 
     */
    public function init()
    {
        $module     = Pi::service('module')->current();
        $formParams = $this->getFormParameters();
        
        // Initializing form defined by user
        foreach (array_keys($formParams) as $name) {
            if (in_array($name, $this->items)) {
                $this->add($formParams[$name]);
            }
        }
        if (in_array('content', $this->items)) {
            $editorConfig = Pi::config()->load("module.{$module}.ckeditor.php");
            $editor       = $this->get('content');
            $editor->setOptions(array_merge(
                $editor->getOptions(),
                $editorConfig
            ));
        }

        // Initializing needed form
        $this->add($formParams['id']);
        $this->add($formParams['fake_id']);
        $this->add($formParams['uid']);
        $this->add($formParams['time_publish']);
        $this->add($formParams['time_update']);
        $this->add($formParams['time_submit']);
        $this->add($formParams['article']);
        $this->add($formParams['jump']);
        
        $this->add(array(
            'name'       => 'security',
            'type'       => 'csrf',
        ));

        $this->add(array(
            'name'       => 'do_submit',
            'attributes' => array(               
                'value'     => __('Save draft'),
            ),
            'type'       => 'submit',
        ));
    }
    
    /**
     * Get form parameters
     * 
     * @return array 
     */
    protected function getFormParameters()
    {
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

        $parameters = array(
            'subject'    => array(
                'name'       => 'subject',
                'options'    => array(
                    'label'     => __('Subject'),
                ),
                'attributes' => array(
                    'id'        => 'subject',
                    'type'      => 'text',
                    'data-size' => $config['max_subject_length'],
                ),
            ),
            
            'subtitle'   => array(
                'name'       => 'subtitle',
                'options'    => array(
                    'label'     => __('Subtitle'),
                ),
                'attributes' => array(
                    'id'        => 'subtitle',
                    'type'      => 'text',
                    'data-size' => $config['max_subtitle_length'],
                ),
            ),
            
            'image'      => array(
                'name'       => 'image',
                'options'    => array(
                    'label'     => __('Image'),
                ),
                'attributes' => array(
                    'id'        => 'image',
                    'type'      => 'hidden',
                ),
            ),
            
            'uid'        => array(
                'name'       => 'uid',
                'options'    => array(
                    'label'     => __('Submitter'),
                ),
                'attributes' => array(
                    'id'        => 'user',
                    'type'      => 'hidden',
                ),
            ),

            'author'     => array(
                'name'       => 'author',
                'options'    => array(
                    'label'     => __('Author'),
                ),
                'attributes' => array(
                    'id'        => 'author',
                    'type'      => 'hidden',
                ),
            ),

            'source'     => array(
                'name'       => 'source',
                'options'    => array(
                    'label'     => __('Source'),
                ),
                'attributes' => array(
                    'id'        => 'source',
                    'type'      => 'text',
                ),
            ),

            'category'   => array(
                'name'       => 'category',
                'options'    => array(
                    'label'     => __('Category'),
                ),
                'type'       => 'Module\Article\Form\Element\Category',
            ),
            
            'related'    => array(
                'name'       => 'related',
                'options'    => array(
                    'label'     => __('Related article'),
                ),
                'attributes' => array(
                    'id'        => 'related',
                    'type'      => 'hidden',
                ),
            ),

            'slug'       => array(
                'name'       => 'slug',
                'options'    => array(
                    'label'     => __('Slug'),
                ),
                'attributes' => array(
                    'id'        => 'slug',
                    'type'      => 'text',
                    'data-size' => $config['max_subject_length'],
                ),
            ),

            'seo_title'  => array(
                'name'       => 'seo_title',
                'options'    => array(
                    'label'     => __('SEO title'),
                ),
                'attributes' => array(
                    'id'        => 'seo_title',
                    'type'      => 'text',
                ),
            ),

            'seo_keywords' => array(
                'name'       => 'seo_keywords',
                'options'    => array(
                    'label'     => __('SEO keywords'),
                ),
                'attributes' => array(
                    'id'        => 'seo_keywords',
                    'type'      => 'text',
                ),
            ),

            'seo_description' => array(
                'name'       => 'seo_description',
                'options'    => array(
                    'label'     => __('SEO description'),
                ),
                'attributes' => array(
                    'id'        => 'seo_description',
                    'type'      => 'textarea',
                ),
            ),

            'time_publish' => array(
                'name'       => 'time_publish',
                'options'    => array(
                    'label'      => __('Publish time'),
                ),
                'attributes' => array(
                    'type'       => 'hidden',
                ),
            ),

            'time_update' => array(
                'name'       => 'time_update',
                'options'    => array(
                    'label'     => __('Update time'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                ),
            ),

            'time_submit' => array(
                'name'       => 'time_submit',
                'options'    => array(
                    'label'     => __('Submit time'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                ),
            ),
            
            'content'    => array(
                'name'       => 'content',
                'options'    => array(
                    'label'     => __('Content'),
                    'editor'    => $editor,
                    'set'       => $set,
                ),
                'attributes' => array(
                    'id'        => 'content',
                    'type'      => 'editor',
                ),
            ),
        
            'id'         => array(
                'name'       => 'id',
                'attributes' => array(
                    'id'        => 'id',
                    'type'      => 'hidden',
                ),
            ),

            'fake_id'    => array(
                'name'       => 'fake_id',
                'attributes' => array(
                    'id'        => 'fake_id',
                    'type'      => 'hidden',
                ),
            ),

            'article'    => array(
                'name'       => 'article',
                'attributes' => array(
                    'id'        => 'article',
                    'type'      => 'hidden',
                ),
            ),

            'jump'       => array(
                'name'       => 'jump',
                'attributes' => array(
                    'id'        => 'jump',
                    'type'      => 'hidden',
                    'value'     => '',
                ),
            ),
        );
        
        if (!empty($config['enable_summary'])) {
            $parameters['summary'] = array(
                'name'       => 'summary',
                'options'    => array(
                    'label'     => __('Summary'),
                ),
                'attributes' => array(
                    'type'      => 'textarea',
                    'data-size' => $config['max_summary_length'],
                ),
            );
        }
        
        if ($config['enable_tag']) {
            $parameters['tag'] = array(
                'name'       => 'tag',
                'type'       => 'tag',
                'options'    => array(
                    'label'     => __('Tags'),
                ),
                'attributes' => array(
                    'id'        => 'tag',
                ),
            );
        }
        
        return $parameters;
    }
}
