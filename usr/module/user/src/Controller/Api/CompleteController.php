<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ApiController;
use Zend\InputFilter\InputFilter;

/**
 * Form webservice controller
 *
 * Methods:
 * 
 * - set: <uid>, <rule>, array(<field>)
 * - get: <uid>, <rule>, array(<data>)
 *
 * @todo The controller has hard-coded customization and will be refactored. - By @taiwen
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CompleteController extends ApiController
{
    /**
     * Rule file name
     */
    const RULE_FILE = 'profile-complete-rule';
    
    /**
     * Fields donot allow user to read
     * @var array 
     */
    protected $protectedFields = array(
        'credential', 'salt'
    );
    
    /**
     * Elements do not need to render
     * @var array 
     */
    protected $skipFields = array(
        'submit', 'redirect',
    );

    /**
     * Get html of form elements as well as their needed asset file url according
     * to `rule` or direct fields
     * 
     * @return ViewModel
     */
    public function getAction()
    {
        $uid  = $this->params('uid', 0);
        if (empty($uid)) {
            return array(
                'status' => false,
            );
        }
        
        $rule = $this->params('rule', '');
        if (empty($rule)) {
            $rule = $this->params('appkey', '');
        }
        $fields = $this->params('field');
        $fields = json_decode($fields, true);
        
        $module = $this->getModule();
        
        // Get filename that include elements definition
        $name = $this->getFormFile($uid, $rule);
        
        // Get form instance, and remove uneed form
        $form = Pi::api('form', $module)->loadForm($name);
        foreach ($this->skipFields as $field) {
            if ($form->has($field)) {
                $form->remove($field);
            }
        }
        
        // Get form value
        $data = $this->getValues($uid, $name);
        $form->setData($data);
        
        // Get needed asset file url of elements
        $elements = $form->getElements();
        $fields   = array_keys($elements);
        $assets   = array(
            'js'  => array(),
            'css' => array(),
        );
        foreach ($fields as $field) {
            if (method_exists($form->get($field), 'requiredAsset')) {
                $asset = $form->get($field)->requiredAsset();
            }
            if (!empty($asset)) {
                foreach ($assets as $type => &$item) {
                    $item = array_merge($item, $asset[$type]);
                }
            }
        }
        foreach ($assets as &$item) {
            $item = array_unique($item);
        }
        
        // Render form to html
        $content = Pi::service('view')->render(
            array(
                'section' => 'component',
                'module'  => 'user',
                'file'    => 'form',
            ),
            array('form' => $form)
        );
        
        return array(
            'status' => true,
            'data'   => $content,
            'assets'  => $assets,
        );
    }
    
    /**
     * Check posted data and save to database if correct
     * 
     * @return ViewModel
     */
    public function setAction()
    {
        $uid  = $this->params('uid', 0);
        $data = $this->params('data');
        if (empty($uid) || empty($data)) {
            return array(
                'status' => false,
            );
        }
        
        $rule = $this->params('rule', '');
        if (empty($rule)) {
            $rule = $this->params('appkey', '');
        }
        $data   = json_decode($data, true);
        $fields = array_keys($data);
        
        $module = $this->getModule();
        
        // Get filename that include elements definition
        $name = $this->getFormFile($uid, $rule);
        
        // Get form instance, and remove uneed form
        $form = Pi::api('form', $module)->loadForm($name);
        foreach ($this->skipFields as $field) {
            if ($form->has($field)) {
                $form->remove($field);
            }
        }
        
        // Get filter
        $filters = Pi::api('form', $module)->loadFilters($name);
        $filter  = new InputFilter;
        foreach ($filters as $row) {
            $filter->add($row);
        }
        $form->setInputFilter($filter);
        $form->setData($data);
        if (!$form->isValid()) {
            // Get needed asset file url of elements
            $elements = $form->getElements();
            $fields   = array_keys($elements);
            $assets   = array(
                'js'  => array(),
                'css' => array(),
            );
            foreach ($fields as $field) {
                if (method_exists($form->get($field), 'requiredAsset')) {
                    $asset = $form->get($field)->requiredAsset();
                }
                if (!empty($asset)) {
                    foreach ($assets as $type => &$item) {
                        $item = array_merge($item, $asset[$type]);
                    }
                }
            }
            foreach ($assets as &$item) {
                $item = array_unique($item);
            }
        
            // Render template
            $content = Pi::service('view')->render(
                array(
                    'section' => 'component',
                    'module'  => 'user',
                    'file'    => 'form',
                ),
                array('form' => $form)
            );
            return array(
                'status' => false,
                'data'   => $content,
                'assets'  => $assets,
            );
        }
        
        // Update user data
        $data = $form->getData();
        $data['last_modified'] = time();
        Pi::api('user', 'user')->updateUser($uid, $data);
        
        return array(
            'status' => true,
        );
    }
    
    /**
     * Get file name include form elements require user to complete according to rule.
     * If rule file not exist or rule is valid, return false to skip
     * 
     * @param string $rule  key value of rule list
     * @param int    $uid
     * @return string|false
     */
    protected function getFormFile($uid, $rule)
    {
        $module = $this->getModule();
        
        // Get rule file
        $file = sprintf(
            '%s/module/%s/config/%s.php',
            Pi::path('custom'),
            $module,
            self::RULE_FILE
        );
        if (!file_exists($file)) {
            $file = sprintf(
                '%s/%s/config/%s.php',
                Pi::path('module'),
                $module,
                self::RULE_FILE
            );
            if (!file_exists($file)) {
                return false;
            }
        }
        $data = include $file;
        
        // Check if condition field is exists or rule is valid
        if (empty($data)
            || !isset($data['rule_field'])
            || !isset($data['items']['default'])
        ) {
            return false;
        }
        
        // Get key of the rule list
        if (!empty($rule)) {
            $key = preg_replace('/:/', '&', $rule);
        } elseif (empty($data['rule_field'])) {
            $key = 'default';
        } else {
            $fields = explode('&', $data['rule_field']);
            $values = Pi::api('user', $module)->get($uid, $fields);
            foreach ($values as $key => &$value) {
                if (!in_array($key, $fields)) {
                    unset($values[$key]);
                }
                $value = $value ?: 'default';
            }
            $key = implode('&', $values);
        }
        
        // Use default rule if it is not found
        if (
            !isset($data['items'])
            || !isset($data['items'][$key])
            || empty($data['items'][$key])
        ) {
            $key = 'default';
        }
        
        $item = $data['items'][$key];
        if (!isset($item['active']) || !$item['active']) {
            return false;
        }
        
        if (isset($item['form_file']) && !empty($item['form_file'])) {
            $result = $data['items'][$key]['form_file'];
        }
        
        return $result;
    }
    
    /**
     * Get user data from database
     * 
     * @param string $name  Name of file define custom form elements
     * @param int    $uid
     * @return array
     */
    protected function getValues($uid, $name)
    {
        if (is_string($name)) {
            $fields = $this->loadConfig($name);
        } else {
            $fields = $name;
        }
        
        $module = $this->getModule();
        $meta   = Pi::registry('field', $module)->read();
        
        foreach ($fields as $key => $value) {
            if (!$value || empty($value['element'])) {
                if ('compound' == $meta[$key]['type']) {
                    $fields[] = $key;
                    unset($fields[$key]);
                }
            }
        }
        $rowset = Pi::user()->get($uid, $fields);
        
        $result = array();
        foreach ($rowset as $key => $value) {
            if ('compound' == $meta[$key]['type']) {
                $rows = array_shift($value);
                foreach ($rows as $field => $row) {
                    $rows[$key . '-' . $field] = $row;
                    unset($rows[$field]);
                }
                $result = array_merge($rows, $result);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Load form specs from field config, supporting custom configs
     *
     * @param string $name
     *
     * @return array
     */
    protected function loadConfig($name)
    {
        $filePath   = sprintf('user/config/form.%s.php', $name);
        $file       = Pi::path('custom/module') . '/' . $filePath;
        if (!file_exists($file)) {
            $file = Pi::path('module') . '/' . $filePath;
        }
        $config     = include $file;
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
            $result[] = $key;
        }

        return $result;
    }
}