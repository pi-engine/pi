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

        return $this->render($element);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function render(ElementInterface $element);
    
    /**
     * Get template content
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
        if (empty($name)) {
            $elementName = $element->getName();
            $name = str_replace(array('_', '.'), '-', $elementName);
        }
        
        if (empty($module)) {
            $module = $element->getOption('module') ?: Pi::service('module')->current();
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
        
        $this->params = $params;
    }
}
