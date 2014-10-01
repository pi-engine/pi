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
use Module\Article\Form\PageForm;

/**
 * Page manipulation APIs
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Page extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'article';
    
    /**
     * Get all conditions
     * 
     * @return array
     */
    public function getConditions()
    {
        $config = $this->loadConfig('rule');
        $rowset = isset($config['condition']) ? $config['condition'] : array();
        foreach ($rowset as $key => $row) {
            $result[$key] = $this->canonizeCondition($row);
        }
        
        return $result;
    }
    
    /**
     * Get condition by name
     * 
     * @param string $name
     * @return array
     */
    public function getCondition($name)
    {
        $rowset = $this->getConditions();
        $result = isset($rowset[$name]) ? $rowset[$name] : array();
        
        return $result;
    }
    
    /**
     * Get forms that use to consist condition
     * 
     * @return array  array('<form_name>' => '<condition_level>')
     */
    public function getConditionForm()
    {
        $result = array();
        
        $meta = $this->loadConfig('form');
        foreach ($meta['field'] as $key => $row) {
            if (isset($row['condition_level'])) {
                $name = isset($row['name']) ? $row['name'] : $key;
                $result[$name] = $row['condition_level'];
            }
        }
        
        return $result;
    }
    
    /**
     * Format given data
     * array('test') => array('test' => '')
     * 
     * @param array $data
     * @return array
     */
    protected function canonizeCondition($data)
    {
        $result = array();
        
        foreach ($data as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                $result[$value] = '';
                continue;
            }
            $result[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Get rules
     * 
     * @return array
     */
    public function getRules()
    {
        $config = $this->loadConfig('rule');
        $result = isset($config['rule']) ? $config['rule'] : array();
        
        return $result;
    }
    
    /**
     * Get rule by name
     * 
     * @param string $name
     * @return array
     */
    public function getRule($name)
    {
        $rowset = $this->getRules();
        $result = isset($rowset[$name]) ? $rowset[$name] : array();
        
        return $result;
    }

    /**
     * Load page related config, the config in custom folder will load first if
     * it exists.
     * 
     * @param string $name   Config file name without `page.` prefix and `.php` suffix
     * @return array
     */
    protected function loadConfig($name)
    {
        $filename = sprintf(
            '%s/module/%s/config/page.%s.php',
            Pi::path('custom'),
            $this->module,
            $name
        );
        if (!file_exists($filename)) {
            $filename = sprintf(
                '%s/%s/config/page.%s.php',
                Pi::path('module'),
                $this->module,
                $name
            );
            if (!file_exists($filename)) {
                return array();
            }
        }
        
        return include $filename;
    }
    
    /**
     * Get condition name by given condition
     * 
     * @param array $needle
     * @return string
     */
    public function searchConditionName(array $needle)
    {
        $result = '';
        
        $count = count($needle);
        $needleQuery = $this->serialize($needle);
        $conditions  = $this->getConditions();
        foreach ($conditions as $name => $cond) {
            if (count($cond) !== $count) {
                continue;
            }
            
            $searchQuery = $this->serialize($cond);
            if ($searchQuery !== $needleQuery) {
                continue;
            }
            $result = $name;
            break;
        }
        
        return $result;
    }
    
    /**
     * Serialize given data
     * 
     * @param array $data
     * @return string
     */
    protected function serialize(array $data)
    {
        ksort($data);
        array_walk($data, function(&$val) {
            if (is_array($val)) {
                sort($val);
            } else {
                $val = (string) $val;
            }
        });
        
        $result = md5(serialize($data));
        
        return $result;
    }
    
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
                $formClassName = 'Module\Article\Form\PageForm';
            }
        }

        if ($withFilter) {
            list($elements, $filters) = $this->loadFields($name, $withFilter);
        } else {
            $elements   = $this->loadFields($name, $withFilter);
            $filters    = array();
        }

        $form = new $formClassName($name, $elements);
        if ($withFilter && $form instanceof PageForm) {
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
        $meta       = $this->loadConfig($name);
        foreach ($meta['field'] as $name => $value) {
            $element = $this->canonizeElement($value);
            if ($element) {
                $elements[] = $element;
            }
            if ($withFilter) {
                $filter = $this->canonizeFilter($value);
                if ($filter) {
                    $filters[] = $filter;
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
        $meta       = $this->loadConfig($name);
        foreach ($meta['field'] as $name => $value) {
            $filter = $this->canonizeFilter($value);
            if ($filter) {
                $filters[] = $filter;
            }
        }

        return $filters;
    }
    
    /**
     * Canonize form element for a field
     *
     * @param array $data
     * @return array
     */
    protected function canonizeElement($data)
    {
        $element = array();
        if (!isset($data['edit'])) {
            $element['type'] = 'text';
        } elseif (is_array($data['edit'])) {
            if (isset($data['edit']['element'])) {
                $element['type'] = $data['edit']['element'];
            } else {
                $element['type'] = 'text';
            }
        } else {
            $element['type'] = $data['edit'];
        }
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

        return $result;
    }
    
    /**
     * Get page action name
     * 
     * @param array $params
     * @return string
     */
    public function getPageAction($params)
    {
        $module = isset($params['module'])
            ? $params['module'] : Pi::service('module')->current();
        
        $class = 'Custom\Article\Api\Page';
        if (class_exists($class)) {
            $handler = new $class($module);
            $action = $handler->parseAction($params, $module);
        } else {
            $action = $this->parseAction($params, $module);
        }
        
        return $action;
    }
    
    /**
     * Parse action name by given parameters.
     * 
     * Note: this method must be overrided in `page` api of custom folder if
     * you want to customize page dress up rule.
     * 
     * @param array  $params
     * @param string $module
     * @return string
     */
    protected function parseAction($params, $module = '')
    {
        $page = array();
        $module = $module ?: $params['module'];
        
        if (isset($params['category'])
            && !is_numeric($params['category'])
        ) {
            $category = Pi::api('api', $module)->getCategoryList();
            foreach ($category as $row) {
                if ($params['category'] == $row['slug']) {
                    $params['category'] = $row['id'];
                    break;
                }
            }
        }
        $pages = Pi::api('api', $module)->getPageList();
        
        if ('list' === $params['controller']
            && 'all' === $params['action']
        ) {
            foreach ($pages as $row) {
                if ('list' !== $row['controller']
                    || 'all' !== $row['action']
                ) {
                    continue;
                }
                
                $meta = json_decode($row['meta'], true);
                if ($meta['category'] == $params['category']) {
                    $page = $row;
                    break;
                }
            }
        } elseif ('category' === $params['controller']
           && 'index' === $params['action']
        ) {
            foreach ($pages as $row) {
                if ('category' !== $row['controller']
                    || 'index' !== $row['action']
                ) {
                    continue;
                }
                
                $meta = json_decode($row['meta'], true);
                if ($meta['category'] == $params['category']) {
                    $page = $row;
                    break;
                }
            }
        } elseif ('article' === $params['controller']
            && 'detail' === $params['action']
        ) {
            foreach ($pages as $row) {
                if ('article' !== $row['controller']
                    || 'detail' !== $row['action']
                ) {
                    continue;
                }
                
                // Get article category
                $article = Pi::model('article', $module)->find($params['id']);
                $meta    = json_decode($row['meta'], true);
                if ($meta['category'] == $article->category) {
                    $page = $row;
                    break;
                }
            }
        }
        
        // Get parent page, if it exists, its page blocks will be used
        if ($page && 1 != $page['depth']) {
            foreach ($pages as $row) {
                if ($row['left'] < $page['left']
                    && $row['right'] > $page['right']
                    && 1 == $row['depth']
                ) {
                    $page = $row;
                    break;
                }
            }
        }
        $action = isset($page['name']) ? $page['name'] : $params['action'];
        
        return $action;
    }
}
