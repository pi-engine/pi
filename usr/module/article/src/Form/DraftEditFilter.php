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
use Zend\InputFilter\InputFilter;
use Module\Article\Controller\Admin\SetupController as Config;
use Module\Article\Form\DraftEditForm;

/**
 * Filter and valid of draft edit form
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class DraftEditFilter extends InputFilter
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
     * Initialize class and filter
     * 
     * @param array $options 
     */
    public function __construct($options = array())
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
            $this->items = DraftEditForm::getDefaultElements($this->mode);
        }

        $filterParams = $this->getFilterParameters();
        foreach (array_keys($filterParams) as $name) {
            if (in_array($name, $this->items)) {
                $this->add($filterParams[$name]);
            }
        }

        $this->add($filterParams['id']);
        $this->add($filterParams['fake_id']);
        $this->add($filterParams['uid']);
        $this->add($filterParams['time_publish']);
        $this->add($filterParams['time_update']);
        $this->add($filterParams['time_submit']);
        $this->add($filterParams['article']);
        $this->add($filterParams['jump']);
    }
    
    /**
     * Get filter parameters
     * 
     * @return array 
     */
    protected function getFilterParameters()
    {
        $module = Pi::service('module')->current();
        $config = Pi::config('', $module);

        $parameters = array(
            'subject'       => array(
                'name'         => 'subject',
                'required'     => false,
                'filters'      => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ),
            
            'subtitle'      => array(
                'name'         => 'subtitle',
                'required'     => false,
                'filters'      => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ),
            
            'image'         => array(
                'name'         => 'image',
                'required'     => false,
            ),
            
            'uid'           => array(
                'name'         => 'uid',
                'required'     => false,
            ),

            'author'        => array(
                'name'         => 'author',
                'required'     => false,
            ),

            'source'        => array(
                'name'         => 'source',
                'required'     => false,
            ),

            'category'      => array(
                'name'         => 'category',
                'required'     => false,
            ),
            
            'related'       => array(
                'name'         => 'related',
                'required'     => false,
            ),

            'slug'          => array(
                'name'         => 'slug',
                'required'     => false,
                'filters'      => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ),

            'seo_title'     => array(
                'name'         => 'seo_title',
                'required'     => false,
            ),

            'seo_keywords'  => array(
                'name'         => 'seo_keywords',
                'required'     => false,
            ),

            'seo_description' => array(
                'name'           => 'seo_description',
                'required'       => false,
            ),

            'time_publish'  => array(
                'name'         => 'time_publish',
                'required'     => false,
            ),

            'time_update'   => array(
                'name'         => 'time_update',
                'required'     => false,
            ),
            
            'time_submit'   => array(
                'name'         => 'time_submit',
                'required'     => false,
            ),

            'content'       => array(
                'name'         => 'content',
                'required'     => false,
            ),

            'id'            => array(
                'name'         => 'id',
                'required'     => false,
            ),

            'fake_id'       => array(
                'name'         => 'fake_id',
                'required'     => false,
            ),

            'article'       => array(
                'name'         => 'article',
                'required'     => false,
            ),

            'jump'          => array(
                'name'         => 'jump',
                'required'     => false,
            ),
        );

        if ($config['enable_summary']) {
            $parameters['summary'] = array(
                'name'       => 'summary',
                'required'   => false,
            );
        }

        if ($config['enable_tag']) {
            $parameters['tag'] = array(
                'name'        => 'tag',
                'required'    => false,
            );
        }

        return $parameters;
    }
}
