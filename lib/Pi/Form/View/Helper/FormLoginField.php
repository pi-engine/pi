<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Pi\Form\Element\LoginField as LoginFieldElement;
use Zend\Form\View\Helper\FormInput;
use Zend\Form\ElementInterface;

/**
 * Login identity element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormLoginField extends FormInput
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        if (!$element instanceof LoginFieldElement) {
            return '';
        }
        $fields = $element->getFields();
        if (1 == count($fields)) {
            $element->setAttribute('placeholder', current($fields));
            return parent::render($element);
        }
        $template = $element->getOption('template')
            ?: '<div class="input-group">%s</div>';

        $pattern =<<<EOT
<input name="%s[0]" %s%s
<span class="input-group-addon">
    <select class="pull-right" name="%s[1]">
        %s
    </select>
</span>
EOT;

        $name = $element->getName();
        list($value, $field) = $element->getValue();
        $attributes = array_replace($element->getAttributes(), array(
            'type'  => 'text',
            'value' => $value,
        ));
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'form-control';
        }
        $attribString = $this->createAttributesString($attributes);

        $patternField = '<option value="%s"%s>%s</option>' . PHP_EOL;
        $fieldString = '';
        foreach ($fields as $key => $label) {
            $class = $field == $key ? ' selected' : '';
            $fieldString .= sprintf($patternField, $key, $class, $label);
        }

        $html = sprintf($template, sprintf(
            $pattern,
            $name,
            $attribString,
            $this->getInlineClosingBracket(),
            $name,
            $fieldString
        ));

        return $html;
    }
}
