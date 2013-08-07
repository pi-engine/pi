<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Element\Checkbox as CheckboxElement;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormInput;
use Zend\Form\View\Helper\FormLabel;

/**
 * Checkbox element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormCheckbox extends FormInput
{
    /**
     * Label position of append
     *
     * @var string
     */
    const LABEL_APPEND  = 'append';

    /**
     * Label position of prepend
     *
     * @var string
     */
    const LABEL_PREPEND = 'prepend';

    /**
     * To render hidden element
     *
     * @var bool
     */
    protected $useHiddenElement = true;

    /** @var FormLabel Lable render helper */
    protected $labelHelper;

    /** @var string Label position */
    protected $labelPosition = self::LABEL_APPEND;

    /**
     * @var array Label attributes
     */
    protected $labelAttributes = array(
        'class' => 'checkbox',
    );

    /**
     * Set value for label position
     *
     * @param  string labelPosition
     * @return self
     */
    public function setLabelPosition($labelPosition)
    {
        $labelPosition = strtolower($labelPosition);
        if (!in_array(
            $labelPosition,
            array(static::LABEL_APPEND, static::LABEL_PREPEND)
        )) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either %s or %s; received "%s"',
                __METHOD__,
                static::LABEL_APPEND,
                static::LABEL_PREPEND,
                (string) $labelPosition
            ));
        }
        $this->labelPosition = $labelPosition;

        return $this;
    }

    /**
     * Get position of label
     *
     * @return string
     */
    public function getLabelPosition()
    {
        return $this->labelPosition;
    }

    /**
     * Sets the attributes applied to option label.
     *
     * @param  array|null $attributes
     * @return self
     */
    public function setLabelAttributes($attributes)
    {
        $this->labelAttributes = $attributes;

        return $this;
    }

    /**
     * Returns the attributes applied to each option label.
     *
     * @return array|null
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes;
    }

    /**
     * Returns the option for prefixing the element with a hidden element
     * for the unset value.
     *
     * @return bool
     */
    public function getUseHiddenElement()
    {
        return $this->useHiddenElement;
    }

    /**
     * Sets the option for prefixing the element with a hidden element
     * for the unset value.
     *
     * @param  bool $useHiddenElement
     * @return self
     */
    public function setUseHiddenElement($useHiddenElement)
    {
        $this->useHiddenElement = (bool) $useHiddenElement;

        return $this;
    }

    /**
     * Render a form <input> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (!$element instanceof CheckboxElement) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type'
                . ' Zend\Form\Element\Checkbox',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name;'
                . ' none discovered',
                __METHOD__
            ));
        }

        $attributes            = $element->getAttributes();
        $attributes['name']    = $name;
        $attributes['type']    = $this->getInputType();
        $attributes['value']   = $element->getCheckedValue();
        $closingBracket        = $this->getInlineClosingBracket();

        if ($element->isChecked()) {
            $attributes['checked'] = 'checked';
        }

        $input = sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $closingBracket
        );

        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $labelHelper      = $this->getLabelHelper();
        $labelClose       = $labelHelper->closeTag();
        $labelPosition    = $this->getLabelPosition();
        $globalLabelAttributes = $element->getLabelAttributes();

        if (empty($globalLabelAttributes)) {
            $globalLabelAttributes = $this->labelAttributes;
        }

        $label = $element->getAttribute('description') ?: $element->getLabel();
        $labelAttributes = $this->labelAttributes
                ?: $element->getLabelAttributes();

        if (null !== ($translator = $this->getTranslator())) {
            $label = $translator->translate(
                $label, $this->getTranslatorTextDomain()
            );
        }

        $label     = $escapeHtmlHelper($label);
        $labelOpen = $labelHelper->openTag($labelAttributes);
        $template  = $labelOpen . '%s%s' . $labelClose;
        switch ($labelPosition) {
            case static::LABEL_PREPEND:
                $rendered = sprintf($template, $label, $input);
                break;
            case static::LABEL_APPEND:
            default:
                $rendered = sprintf($template, $input, $label);
                break;
        }

        // Render hidden element
        $useHiddenElement = (null !== $this->useHiddenElement)
            ? $this->useHiddenElement
            : (method_exists($element, 'useHiddenElement')
                ? $element->useHiddenElement()
                : false);

        if ($useHiddenElement) {
            $hiddenAttributes = array(
                'name'  => $attributes['name'],
                'value' => $element->getUncheckedValue(),
            );

            $rendered = sprintf(
                '<input type="hidden" %s%s',
                $this->createAttributesString($hiddenAttributes),
                $closingBracket
            ) . $rendered;
        }

        return $rendered;
    }

    /**
     * Return input type
     *
     * @return string
     */
    protected function getInputType()
    {
        return 'checkbox';
    }


    /**
     * Retrieve the FormLabel helper
     *
     * @return FormLabel
     */
    protected function getLabelHelper()
    {
        if ($this->labelHelper) {
            return $this->labelHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->labelHelper = $this->view->plugin('form_label');
        }

        if (!$this->labelHelper instanceof FormLabel) {
            $this->labelHelper = new FormLabel();
        }

        return $this->labelHelper;
    }
}
