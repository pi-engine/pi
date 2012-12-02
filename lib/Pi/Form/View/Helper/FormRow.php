<?php
/**
 * Form element row view helper
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Form
 * @subpackage      View
 * @version         $Id$
 */

namespace Pi\Form\View\Helper;

use Zend\Form\View\Helper\FormRow as ZendFormRow;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\Element\Collection;
use Zend\Form\FieldsetInterface;

class FormRow extends ZendFormRow
{
    protected $descriptionHelper;

    /**
     * @var string
     */
    protected $inputErrorClass = 'input-error alert-error';

    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @param ElementInterface $element
     * @return string
     * @throws \Zend\Form\Exception\DomainException
     */
    public function render(ElementInterface $element)
    {
        if ($element instanceof Collection) {
            return $this->view->formCollection($element);
        } elseif ($element instanceof FieldsetInterface) {
            return $this->view->formFieldset($element);
        }

        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();


        $label           = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();
        $errorAttributes = array();
        if (!empty($inputErrorClass)) {
            $errorAttributes['class'] = $inputErrorClass;
        }
        $elementErrors   = $elementErrorsHelper->render($element, $errorAttributes);

        // Does this element have errors ?
        if (!empty($elementErrors) && !empty($inputErrorClass)) {
            $classAttributes = ($element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '');
            $classAttributes = $classAttributes . $inputErrorClass;

            $element->setAttribute('class', $classAttributes);
        }

        $elementString = $elementHelper->render($element);

        $descriptionHelper   = $this->getDescriptionHelper();
        $elementDescription = $descriptionHelper->render($element);

        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label, $this->getTranslatorTextDomain()
                );
            }

            $label = $escapeHtmlHelper($label);
            $labelAttributes = $element->getLabelAttributes();

            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }

            // Multicheckbox elements have to be handled differently as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            $type = $element->getAttribute('type');
            if ($type === 'multi_checkbox' || $type === 'multicheckbox' || $type === 'radio') {
                $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s%s</fieldset>',
                    $label,
                    $elementDescription,
                    $elementString);
            } else {
                if ($element->hasAttribute('id')) {
                    $labelOpen = $labelHelper($element);
                    $labelClose = '';
                    $label = '';
                } else {
                    $labelOpen  = $labelHelper->openTag($labelAttributes);
                    $labelClose = $labelHelper->closeTag();
                }

                $markup = '<dt>' . $labelOpen . $label . $labelClose . '</dt>' . $elementDescription . $elementString;
                /*
                switch ($this->labelPosition) {
                    case static::LABEL_PREPEND:
                        $markup = $labelOpen . $label . $elementDescription .  $elementString . $labelClose . $elementErrors;
                        break;
                    case static::LABEL_APPEND:
                    default:
                        $markup = $labelOpen . $elementString . $label . $elementDescription . $labelClose . $elementErrors;
                        break;
                }
                */
            }

            if ($this->renderErrors) {
                $markup .= $elementErrors;
            }
        } else {
            $elementString = $elementDescription . $elementString;
            if ($this->renderErrors) {
                $markup = $elementString . $elementErrors;
            } else {
                $markup = $elementString;
            }
        }

        return $markup;
    }

    /**
     * Retrieve the FormDescription helper
     *
     * @return FormDescription
     */
    protected function getDescriptionHelper()
    {
        if ($this->descriptionHelper) {
            return $this->descriptionHelper;
        }
        $this->descriptionHelper = $this->view->plugin('form_description');
        return $this->descriptionHelper;
    }
}
