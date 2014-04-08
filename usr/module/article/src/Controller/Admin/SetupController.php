<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Article\Form\DraftCustomForm;
use Module\Article\Form\DraftCustomFilter;
use Module\Article\Form\DraftEditForm;
use Module\Article\Rule;

/**
 * Config controller
 * 
 * Feature list:
 * 
 * 1. Custom config the form to display in draft edit page
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class SetupController extends ActionController
{
    const FORM_MODE_NORMAL   = 'normal';
    const FORM_MODE_EXTENDED = 'extension';
    const FORM_MODE_CUSTOM   = 'custom';

    /**
     * Get config file name
     *
     * @param bool $custom
     * @param string $module
     *
     * @return string
     */
    public static function getFilename($custom = false, $module = '')
    {
        $identifier = $custom ? 'custom.form' : 'form';
        $module     = $module ?: Pi::service('module')->current();
        $filename = sprintf('%s.%s.php', $module, $identifier);
        
        return $filename;
    }
    
    /**
     * Read configuration of form for displaying from file define by user
     * 
     * @return array 
     */
    public static function getFormConfig()
    {
        $filename = self::getFilename();
        $config = Pi::config()->load($filename);
        if (empty($config)) {
            return array();
        }
        
        if (self::FORM_MODE_CUSTOM != $config['mode']) {
            $config['elements'] = DraftEditForm::getDefaultElements($config['mode']);
        }
        
        return $config;
    }
    
    /**
     * Default action, jump to form configuration page
     * 
     * @return ViewModel 
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array('action' => 'form'));
    }

    /**
     * Config whether to display form in draft edit page
     * 
     * @return ViewModel 
     */
    public function formAction()
    {
        // Get exists and needed elements
        $items          = DraftEditForm::getExistsFormElements();
        $neededElements = DraftEditForm::getNeededElements();
        
        // Get custom elements
        $filename = self::getFilename(true);
        $customElements = Pi::config()->load($filename);
        $customElements = $customElements ?: $neededElements;
        
        // Render form
        $params = array(
            'elements'  => $items,
            'custom'    => $customElements,
        );
        $form = new DraftCustomForm('custom', $params);
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => 'form')),
            'class'   => 'form-horizontal',
        ));
        $options = self::getFormConfig();
        $data = array();
        if (!empty($options)) {
            $data['mode'] = $options['mode'];
            if (self::FORM_MODE_CUSTOM == $options['mode']) {
                foreach ($options['elements'] as $value) {
                    $data[$value] = 1;
                }
            }
            $form->setData($data);
        }
        foreach ($neededElements as $element) {
            $form->get($element)->setAttribute('disabled', 'disabled');
        }
        
        $this->view()->assign(array(
            'title'     => _a('Form Configuration'),
            'form'      => $form,
            'custom'    => self::FORM_MODE_CUSTOM,
            'action'    => 'form',
            'items'     => $items,
        ));
        
        if ($this->request->isPost()) {
            $post = (array) $this->request->getPost();
            foreach ($neededElements as $need) {
                $post[$need] = 1;
            }
            $form->setData($post);
            $form->setInputFilter(
                new DraftCustomFilter($post['mode'], 
                array('needed' => $neededElements))
            );

            if (!$form->isValid()) {
                return $this->renderForm(
                    $form, 
                    _a('Items marked red is required!')
                );
            }
            
            $data = $form->getData();
            
            // Update custom form elements
            $elements = array();
            foreach (array_keys($items) as $name) {
                if (!empty($data[$name])) {
                    $elements[] = $name;
                }
            }
            if (self::FORM_MODE_CUSTOM == $data['mode']) {
                $this->updateCustomElement($elements);
            }
                
            // Insert configuration into file
            if (self::FORM_MODE_CUSTOM != $data['mode']) {
                $elements = $data['mode'];
            }
            $options = array(
                self::FORM_MODE_NORMAL   => DraftEditForm::getDefaultElements(
                    self::FORM_MODE_NORMAL
                ),
                self::FORM_MODE_EXTENDED => DraftEditForm::getDefaultElements(
                    self::FORM_MODE_EXTENDED
                ),
            );
            $result  = $this->saveFormConfig($elements);
            if (!$result) {
                return $this->renderForm(
                    $form,
                    _a('Can not save data!')
                );
            }
            
            $this->renderForm(
                $form,
                _a('Data saved successful!'),
                false
            );
        }
    }
    
    /**
     * Update custom form file
     */
    public function updateAction()
    {
        Pi::service('log')->mute();
        
        $elements = $this->params('elements', '');
        $elements = explode(',', $elements);
        
        $result = $this->updateCustomElement($elements);
        
        echo json_encode(array(
            'status'    => $result,
            'message'   => $result ? _a('Success!') : _a('Can not save file!'),
        ));
        exit;
    }
    
    /**
     * Preview draft edit page
     * 
     * @return ViewModel 
     */
    public function previewAction()
    {
        // Get elements
        $options = array();
        $mode    = $this->params('mode', '');
        if (empty($mode)) {
            return $this->jumpTo404(_a('Invalid mode!'));
        }
        $options['mode'] = $mode;
        
        if ('custom' == $mode) {
            $elements = $this->params('elements', '');
            $options['elements'] = explode(',', $elements);
        } else {
            $options['elements'] = DraftEditForm::getDefaultElements($mode);
        }
        
        $form = new DraftEditForm('add', $options);
        
        // Get allowed categories
        $rules        = Rule::getPermission();
        $listCategory = array();
        $approve      = array();
        $delete       = array();
        foreach ($rules as $key => $rule) {
            if (isset($rule['compose']) and $rule['compose']) {
                $listCategory[$key] = true;
            }
            if (isset($rule['approve']) and $rule['approve']) {
                $approve[] = $key;
            }
            if (isset($rule['approve-delete']) and $rule['approve-delete']) {
                $delete[] = $key;
            }
        }
        
        $categories = $form->get('category')->getValueOptions();
        $form->get('category')
            ->setValueOptions(array_intersect_key($categories, $listCategory));
        
        $form->setData(array(
            'category'      => $this->config('default_category'),
            'source'        => $this->config('default_source'),
            'fake_id'       => uniqid(),
            'uid'           => Pi::user()->getId(),
        ));
        
        $module = $this->getModule();
        $this->view()->assign(array(
            'form'      => $form,
            'config'    => Pi::config('', $module),
            'elements'  => $options['elements'],
            'rules'     => $rules,
            'approve'   => $approve,
            'delete'    => $delete,
            'status'    => \Module\Article\Model\Draft::FIELD_STATUS_DRAFT,
            'currentDelete' => true,
        ));
        $this->view()->setTemplate('draft-edit', $this->getModule(), 'front');
    }
    
    /**
     * Render form
     * 
     * @param Zend\Form\Form $form     Form instance
     * @param string         $message  Message assign to template
     * @param bool           $error    Whether is error message
     */
    public function renderForm($form, $message = null, $error = true)
    {
        $params = compact('form', 'message', 'error');
        $this->view()->assign($params);
    }
    
    /**
     * Saving config result into file
     * 
     * @param array|int  $elements     Elements want to display, if mode is 
     *                                 not custom, its value is mode name
     * @return bool 
     */
    protected function saveFormConfig($elements)
    {
        $configs = array();
        if (is_string($elements)) {
            $configs['mode'] = $elements;
        } else {
            $configs = array(
                'mode'      => self::FORM_MODE_CUSTOM,
                'elements'  => $elements,
            );
        }
        
        $filename = self::getFilename();
        $result = Pi::config()->write($filename, $configs, true);
        
        return $result;
    }
    
    /**
     * Update custom elements
     * 
     * @param array  $elements
     * @return bool 
     */
    protected function updateCustomElement($elements)
    {
        $filename = static::getFilename(true);
        $result   = Pi::config()->write($filename, $elements, true);
        
        return $result;
    }
}
