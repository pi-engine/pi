<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Detail controller
 */
class DetailController extends ActionController
{
    // Rule file name
    const RULE_FILE = 'profile-complete-rule';
    
    /**
     * Detail complete action
     *
     * 1. Display detail complete form
     * 2. Save user information
     * 3. Sign user data
     */
    public function completeAction()
    {
        $result = array('status' => 0);

        $redirect = $this->params('redirect', $_SERVER['HTTP_REFERER']);
        
        Pi::service('authentication')->requireLogin();
        $uid = Pi::user()->getId();

        // Get name of file that include customized form
        $rule = $this->params('rule', '');
        $file = $this->getFormFile($rule, $uid);
        if (!$file) {
            return $this->redirect($redirect);
        }
        $form = Pi::api('form', 'user')->loadForm($file);
        $values = $this->getValues($file, $uid);
        $values['redirect'] = $redirect;
        $form->setAttribute('action', $this->url('', array(
            'action' => 'complete',
            'rule'   => $rule,
        )));
        $form->setData($values);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->loadInputFilter();
            $form->setData($post);

            if ($form->isValid()) {
                $data     = $form->getData();
                $redirect = $data['redirect'];
                unset($data['redirect']);
                $data['last_modified'] = time();
                Pi::api('user', 'user')->updateUser($uid, $data);

                return $this->redirect($redirect);
            } else {
                $this->view()->assign('result', $result);
            }
        }

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('register-profile-complete');
    }
    
    /**
     * Get file name include form elements require user to complete according to rule.
     * If rule file not exist or rule is valid, return false to skip
     * 
     * @param string $rule  key value of rule list
     * @param int    $uid
     * @return string|false
     */
    protected function getFormFile($rule, $uid = 0)
    {
        // Get rule file
        $file = sprintf(
            '%s/module/%s/config/%s.php',
            Pi::path('custom'),
            $this->getModule(),
            self::RULE_FILE
        );
        if (!file_exists($file)) {
            $file = sprintf(
                '%s/%s/config/%s.php',
                Pi::path('module'),
                $this->getModule(),
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
            $uid    = $uid ?: Pi::service('user')->getId();
            $fields = explode('&', $data['rule_field']);
            $values = Pi::api('user', $this->module)->get($uid, $fields);
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
    
    /**
     * Get user data from database
     * 
     * @param string $name  Name of file define custom form elements
     * @param int    $uid
     * @return array
     */
    protected function getValues($name, $uid = 0)
    {
        $fields = $this->loadConfig($name);
        
        $uid    = $uid ?: Pi::user()->getId();
        $rowset = Pi::api('user', 'user')->get($uid, $fields);
        
        $module = $this->getModule();
        $meta   = Pi::registry('field', $module)->read();
        
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
}
