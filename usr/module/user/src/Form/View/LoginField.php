<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Module\User\Form\View;

use Module\User\Form\Element\LoginField as LoginFieldElement;
use Zend\Form\View\Helper\FormInput;
use Zend\Form\ElementInterface;

/**
 * Login identity element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoginField extends FormInput
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

        $pattern =<<<EOT
<div class="input-group">
    <input name="%s[0]" %s>
    <span class="input-group-addon">
        <select class="pull-right" name="%s[1]">
            %s
        </select>
    </span>
</div>
EOT;

        $name = $element->getName();
        list($value, $field) = $element->getValue();

        $attributes          = $element->getAttributes();
        $attributes['type']  = 'text';
        $attributes['class'] = 'form-control';
        $attributes['value'] = $value;
        $attribString = $this->createAttributesString($attributes);

        $patternField = '<option value="%s"%s>%s</option>' . PHP_EOL;
        $fieldString = '';
        foreach ($fields as $key => $label) {
            $class = $field == $key ? ' selected' : '';
            $fieldString .= sprintf($patternField, $key, $class, $label);
        }

        $html = sprintf(
            $pattern,
            $name,
            $attribString,
            $name,
            $fieldString
        );

        return $html;
    }
}
