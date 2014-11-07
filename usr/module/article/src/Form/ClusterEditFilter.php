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

/**
 * Filter and validator of cluster edit form
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ClusterEditFilter extends InputFilter
{
    /**
     * Initializing validator and filter 
     */
    public function __construct($options = array())
    {
        $this->add(array(
            'name'     => 'parent',
            'required' => true,
        ));

        $params = array(
            'table'  => 'cluster',
        );
        if (isset($options['id']) and $options['id']) {
            $params['id'] = $options['id'];
        }

        $this->add(array(
            'name'     => 'slug',
            'required' => false,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators' => array(
                array(
                    'name'    => 'Module\Article\Validator\RepeatSlug',
                    'options' => $params,
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'title',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'description',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'image',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'id',
            'required' => false,
        ));
        
        $customFilters = $this->getCustomFilters();
        foreach ($customFilters as $filter) {
            $this->add($filter);
        }
    }
    
    /**
     * Get custom filters
     * 
     * @return array
     */
    protected function getCustomFilters()
    {
        $module = Pi::service('module')->current();
        $config = Pi::api('cluster', $module)->loadConfig();
        
        $filters = array();
        foreach ($config['field'] as $filter) {
            $filters[] = $this->canonizeFilter($filter);
        }
        
        return $filters;
    }
    
    /**
     * Canonize form element filter for a field
     *
     * @param array $data
     * @return array
     */
    protected function canonizeFilter($data)
    {
        $result = array();
        if (!empty($data['name'])) {
            $result['name'] = $data['name'];
        }
        if (!empty($data['filter'])) {
            $result['type'] = array_pop($data['filter']);
        }
        if (!empty($data['edit']['filters'])) {
            $result['filters'] = $data['edit']['filters'];
        }
        if (!empty($data['edit']['validators'])) {
            $result['validators'] = $data['edit']['validators'];
        }
        if (!empty($data['allow_empty'])) {
            $result['allow_empty'] = $data['allow_empty'];
        }
        if (!empty($data['is_required'])) {
            $result['required']= $data['is_required'];
        } else {
            $result['required']= 0;
        }
        
        // Enabled validator callback
        $callback = isset($result['validators']['callback'])
            ? $result['validators']['callback'] : '';
        if (!empty($callback)) {
            if (is_string($callback)
               && (false !== strpos($callback, '::'))) {
                $callback = explode('::', $callback);
            }
            $validators = $callback[0]::$callback[1]();
            unset($result['validators']['callback']);
            $result['validators'] = array_merge($result['validators'], $validators);
        }
        unset($callback);
        
        // Enabled filter callback
        $callback = isset($result['filters']['callback'])
            ? $result['filters']['callback'] : '';
        if (!empty($callback)) {
            if (is_string($callback)
               && (false !== strpos($callback, '::'))) {
                $callback = explode('::', $callback);
            }
            $filters = $callback[0]::$callback[1]();
            unset($result['filters']['callback']);
            $result['filters'] = array_merge($result['filters'], $filters);
        }

        return $result;
    }
}
