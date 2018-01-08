<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Pi\Form\View\Helper\FormElement as ZendFormElement;
use Zend\Form\ElementInterface;

/**
 * Form element helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormMedia extends FormElement
{
    /**
     * Render an element
     *
     * {@inheritdoc}
     */
    public function render(ElementInterface $element)
    {
         ini_set('display_errors', 1);
 error_reporting(E_ALL);
        $parsePattern = function ($pattern, $vars) {
            $params = array();
            $vals   = array();
            foreach ($vars as $var => $val) {
                $params[]   = '%' . $var . '%';
                $vals[]     = $val;
            }
            $result = str_replace($params, $vals, $pattern);
            return $result;
        };
        $renderPattern = <<<EOT
<div class="form-group%error_class% has-feedback" data-name="%element_name%">
    %label_html%
    %element_html%
</div>
EOT;
            $labelPattern = <<<EOT
<label class="%label_size% control-label">
    %mark_required%%label_content%
</label>
EOT;

  $descPattern = <<<EOT
<div class="text-muted">%desc_content%</div>
EOT;

        $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    %element_content%
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    %desc_html%
</div>

<div class="%error_size% help-block with-errors">%error_content%</div>
EOT;

 $vars['element_name']       = $element->getName();
$vars['element_content']    = $this->view->formElement($element);
$vars['error_content']      = $this->view->formElementErrors($element);
$vars['error_class']        = $element->getMessages() ? ' has-error' : '';
$vars['desc_content']       = $element->getAttribute('description') . ($element->getAttribute('required') && !$element->getLabel() ? $markRequired : '');
$vars['desc_html']          = $parsePattern($descPattern, $vars);
$vars['label_content']      = $element->getLabel();
$vars['mark_required']      = $element->getAttribute('required') && $element->getLabel() ? $markRequired : '';
$vars['label_html']         = $parsePattern($labelPattern, $vars);
$vars['element_html']       = $parsePattern($elementPattern, $vars);
 
            $rendered = $parsePattern($renderPattern, $vars);

            return $rendered;       
            
             $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        $type = $element->getAttribute('type');
        if ($type) {
            if (false === strpos($type, '\\')) {
                $type = sprintf('form_%s', str_replace('-', '_', $type));
            }
            $helper = $renderer->plugin($type);
            if ($helper) {
                return $helper($element);
            }
        }

        return parent::render($element);
    }
}
