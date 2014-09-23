<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Module\Article\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;
use Pi;

/**
 * Class provided basic method for rendering form element
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
abstract class AbstractCustomHelper extends AbstractHelper
{
    /**
     * Template parameters
     * @var array 
     */
    protected $params = array();
    
    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|self
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }
        
        // Add predefined parameters
        $params['name']       = $element->getName();
        $params['value']      = $element->getValue();
        $params['attributes'] = $this->createAttributesString($element->getAttributes());
        $this->assign($params);

        return $this->render($element);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function render(ElementInterface $element);
    
    /**
     * Get template content
     * 
     * Template priority:
     * - If $name and $module are not empty, template will be used according to them
     * - Or else, if `name` and `module` fields of `render` array in element options
     *   is not empty, template will be used by them
     * - Or else, element name will be used as template name, current requested
     *   module will be used as module
     * 
     * @param string  $name    Template name
     * @param string  $module  Module name
     * @return string
     */
    protected function getTemplate(
        ElementInterface $element,
        $name = '',
        $module = ''
    ) {
        $options = $element->getOptions();
        $render  = isset($options['render']) ? $options['render'] : array();
        
        if (empty($name)) {
            $name = isset($render['name']) ? $render['name'] : '';
            if (empty($name)) {
                $elementName = $element->getName();
                $name = str_replace(array('_', '.'), '-', $elementName);
            }
        }
        
        if (empty($module)) {
            $module = isset($options['render']['module']) ? $options['render']['module'] : '';
            if (empty($module)) {
                // Current requested module must be assign to options, or else error will occur
                // if the helper is used in other module. i.e.: use element in module configuration
                $module = $element->getOption('module') ?: Pi::service('module')->current();
            }
        }
        
        // Get template path
        $template = sprintf(
            '%s/module/%s/template/front/helper/%s.phtml',
            Pi::path('custom'),
            $module,
            $name
        );
        if (!file_exists($template)) {
            $template = sprintf(
                '%s/article/template/front/helper/%s.phtml',
                Pi::path('module'),
                $name
            );
            if (!file_exists($template)) {
                $template = '';
            }
        }
        
        foreach ($this->params as $key => $val) {
            $variable = lcfirst(str_replace(' ', '', ucwords(str_replace(
                array('_', '-'), ' ', $key))));
            $$variable = $val;
        }
        if (empty($template)) {
            return '';
        }
        ob_start();
        include $template;
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    /**
     * Set params for template
     * 
     * Predefine parameters:
     * - `name`: form unique name
     * - `value`: form value
     * - `attributes`: form attributes string, format: key1="value1" key2="value2"
     * 
     * @param array|string $params
     * @param mix $value
     */
    protected function assign($params, $value = '')
    {
        if (is_string($params)) {
            $params = array($params => $value);
        }
        $params = (array) $params;
        
        foreach (array_keys($params) as $key) {
            if (!preg_match_all('/^[a-z][a-z_-]*$/', $key)) {
                unset($params[$key]);
            }
        }
        
        $this->params = array_merge($this->params, $params);
    }
}
