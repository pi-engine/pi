<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Module\Article\Form\DraftForm;

/**
 * Article draft form manipulation APIs
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Form extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'article';

    /**
     * Load form instance, supporting custom form
     *
     * @param string $name
     * @param bool $withFilter  To set InputFilter
     *
     * @return DraftForm
     */
    public function loadForm($name, $withFilter = false)
    {
        $class = str_replace(' ', '', ucwords(
            str_replace(array('-', '_', '.', '\\', '/'), ' ', $name)
        ));
        $formClass = $class . 'Form';
        $formClassName = 'Custom\Article\Form\\' . $formClass;
        if (!class_exists($formClassName)) {
            $formClassName = 'Module\Article\Form\\' . $formClass;
            if (!class_exists($formClassName)) {
                $formClassName = 'Module\Article\Form\DraftForm';
            }
        }

        if ($withFilter) {
            list($elements, $filters) = $this->loadFields($name, $withFilter);
        } else {
            $elements   = $this->loadFields($name, $withFilter);
            $filters    = array();
        }

        $form = new $formClassName($name, $elements);
        if ($withFilter && $form instanceof DraftForm) {
            $form->loadInputFilter($filters);
        }

        return $form;
    }

    /**
     * Load form elements from field config, supporting custom configs
     *
     * @param string $name
     * @param bool $withFilter  To return filters
     *
     * @return array
     */
    public function loadFields($name, $withFilter = false)
    {
        $elements   = array();
        $filters    = array();
        $config     = $this->loadConfig($name);
        $meta       = Pi::registry('field', $this->module)->read();
        foreach ($config as $name => $value) {
            if (!$value || empty($value['element'])) {
                if (isset($meta[$name]) &&
                    $meta[$name]['type'] == 'compound'
                ) {
                    if (is_array($value)) {
                        $fields = $value;
                    }
                    $compoundElements = $this->getCompoundElement($name, $fields);
                    foreach ($compoundElements as $element) {
                        if ($element) {
                            $element['name'] = $this->assembleCompoundFieldName($name, $element['name']);
                            $elements[] = $element;
                        }
                    }
                    if ($withFilter) {
                        $compoundFilters = $this->getCompoundFilter($name, $fields);
                        foreach ($compoundFilters as $filter) {
                            if ($filter) {
                                $filter['name'] = $this->assembleCompoundFieldName($name, $filter['name']);
                                $filters[] = $filter;
                            }
                        }
                    }
                } else {
                    $element = $this->getElement($name);
                    if ($element) {
                        $elements[] = $element;
                    }
                    if ($withFilter) {
                        $filter = $this->getFilter($name);
                        if ($filter) {
                            $filters[] = $filter;
                        }
                    }
                }
            } else {
                if (!empty($value['element'])) {
                    if (empty($value['element']['name']) && is_string($name)) {
                        $value['element']['name'] = $name;
                    }
                    $elements[] = $value['element'];
                }
                if ($withFilter) {
                    if (!empty($value['filter'])) {
                        if (empty($value['filter']['name']) && is_string($name)) {
                            $value['filter']['name'] = $name;
                        }
                        $filters[] = $value['filter'];
                    }
                }
            }
        }

        if ($withFilter) {
            $result = array($elements, $filters);
        } else {
            $result = $elements;
        }

        return $result;
    }

    /**
     * Load form filters from config, supporting custom configs
     *
     * @param string $name
     *
     * @return array
     */
    public function loadFilters($name)
    {
        $filters    = array();
        $config     = $this->loadConfig($name);
        $meta       = Pi::registry('field', $this->module)->read();
        foreach ($config as $name => $value) {
            if (!$value || empty($value['element'])) {
                if (isset($meta[$name]) &&
                    $meta[$name]['type'] == 'compound'
                ) {
                    $compoundFilters = $this->getCompoundFilter($name);
                    foreach ($compoundFilters as $filter) {
                        if ($value && !in_array($filter['name'], $value)) {
                            continue;
                        }
                        if ($filter) {
                            $filter['name'] = $this->assembleCompoundFieldName($name, $filter['name']);
                            $filters[] = $filter;
                        }
                    }
                } else {
                    $filter = $this->getFilter($name);
                    if ($filter) {
                        $filters[] = $filter;
                    }
                }
            } else {
                if (!empty($value['filter'])) {
                    if (empty($value['filter']['name']) && is_string($name)) {
                        $value['filter']['name'] = $name;
                    }
                    $filters[] = $value['filter'];
                }
            }
        }

        return $filters;
    }

    /**
     * Load form specs from field config, supporting custom configs
     *
     * @param string $name
     *
     * @return array
     */
    protected function loadConfig($name = 'draft')
    {
        $filePath   = sprintf('%s/config/form.%s.php', $this->module, $name);
        $file       = Pi::path('custom/module') . '/' . $filePath;
        if (!file_exists($file)) {
            $filePath   = sprintf('article/config/form.%s.php', $name);
            $file = Pi::path('module') . '/' . $filePath;
        }
        $config     = include $file;
        
        // Added default form elements
        $defaultCols = array('subject', 'content', 'time_publish');
        $config      = array_merge($config, $defaultCols);
        $config      = array_unique($config);
        
        $result     = array();
        foreach ($config as $key => $value) {
            if (false === $value) {
                continue;
            }
            if (!is_string($key)) {
                if (!$value) {
                    continue;
                }
                if (is_string($value)) {
                    $key    = $value;
                    $value  = array();
                }
            }
            $result[$key] = (array) $value;
        }

        return $result;
    }

    /**
     * Canonize form element for a field
     *
     * @param array $data
     * @return array
     */
    protected function canonizeElement($data)
    {
        $element = isset($data['edit']['element']) ? $data['edit']['element'] : array();
        $element['name'] = $data['name'];
        if (isset($data['edit']['options']) &&
            $data['edit']['options']
        ) {
            $element['options'] = $data['edit']['options'];
        } else {
            $element['options'] = array();
        }
        $element['options']['label'] = $data['title'];
        if (isset($data['edit']['attributes'])) {
            $element['attributes'] = $data['edit']['attributes'];
        }

        if (isset($data['is_required'])) {
            $element['attributes']['required']= $data['is_required'];
        }
        if (!empty($element['type']) && 'multi_checkbox' == $element['type']) {
            $element['attributes']['required']= 0;
        }

        return $element;
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
        
        /*
        if (!empty($data['edit']['element']['type'])
            && 'multi_checkbox' == $data['edit']['element']['type']
        ) {
            $result['required']= empty($data['is_required']) ? 0 : 1;
        }
        */

        return $result;
    }

    /**
     * Get form element for field
     *
     * @param string $name
     * @return array
     */
    public function getElement($name)
    {
        $element = array();
        $elements = Pi::registry('field', $this->module)->read();
        if (isset($elements[$name]) && isset($elements[$name]['edit'])) {
            $element = $this->canonizeElement($elements[$name]);
        }

        return $element;
    }

    /**
     * Get form filter for field
     *
     * @param string $name
     * @return array
     */
    public function getFilter($name)
    {
        $result = array(
            'name'  => $name,
        );
        $elements = Pi::registry('field', $this->module)->read();
        if (isset($elements[$name]) && isset($elements[$name]['edit'])) {
            $result = $this->canonizeFilter($elements[$name]);
        }

        return $result;
    }

    /**
     * Get a compound field element if specified, or a compound's all fields
     * if field name is not specified
     *
     * @param string $compound
     * @param string $field
     * @return array
     */
    public function getCompoundElement($compound, $field = '')
    {
        $result = array();
        $elements = Pi::registry('compound_field', $this->module)->read($compound);
        if ($field) {
            $fields = (array) $field;
            foreach ($fields as $name) {
                if (isset($elements[$name])) {
                    $element = $this->canonizeElement($elements[$name]);
                    $result[$name] = $element;
                }
            }
            if (is_scalar($field)) {
                $result = $result[$field];
            }
        } else {
            foreach ($elements as $name => $element) {
                $element = $this->canonizeElement($element);
                $result[$name] = $element;
            }
        }

        return $result;
    }

    /**
     * Get a compound field element if specified, or a compound's all fields
     * if field name is not specified
     *
     * @param string $compound
     * @param string $field
     * @return array
     */
    public function getCompoundFilter($compound, $field = '')
    {
        $result = array();
        $elements = Pi::registry('compound_field', $this->module)->read($compound);
        if ($field) {
            $fields = (array) $field;
            foreach ($fields as $name) {
                if (isset($elements[$name])) {
                    $filter = $this->canonizeFilter($elements[$name]);
                    $result[$name] = $filter;
                }
            }
            if (is_scalar($field)) {
                $result = $result[$field];
            }
        } else {
            foreach ($elements as $name => $element) {
                $filter = $this->canonizeFilter($element);
                $result[$name] = $filter;
            }
        }

        return $result;
    }

    /**
     * Assemble a compound field name by prepending compound name
     *
     * @param string $compound
     * @param string $field
     *
     * @return string
     */
    public function assembleCompoundFieldName($compound, $field)
    {
        return $compound . '-' . $field;
    }

    /**
     * Parse compound field name
     *
     * @param string $name
     *
     * @return string[]|bool
     */
    public function parseCompoundFieldName($name)
    {
        $compounds = array_keys(Pi::registry('field', $this->module)->read('compound'));
        $lookup = function ($name, $compounds) {
            $result = false;
            foreach ($compounds as $cName) {
                if (0 === strpos($name, $cName . '-')) {
                    $result = explode('-', $name, 2);
                    break;
                }
            }
            return $result;
        };

        return $lookup($name, $compounds);
    }
    
    /**
     * Get all fields want to show in draft edit page
     * 
     * @param string $name
     * @return array
     */
    public function getDisplayFields($name = 'draft')
    {
        return $this->loadConfig($name);
    }
    
    /**
     * Check if given key is allowed to show
     * @param type $key
     * @param type $name
     * @return type
     */
    public function isDisplayField($key, $name = 'draft')
    {
        $compound = $this->parseCompoundFieldName($key);
        $fields   = $this->getDisplayFields($name);
        if (false === $compound) {
            return isset($fields[$key]);
        }
        
        return (bool) in_array($compound[1], $fields[$compound[0]]);
    }
}
