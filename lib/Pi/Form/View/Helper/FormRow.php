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

use Zend\Form\View\Helper\FormRow as ZendFormRow;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\Element\Collection;
use Zend\Form\FieldsetInterface;

/**
 * Element row helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormRow extends ZendFormRow
{
    /** @var string CSS class for error display */
    protected $inputErrorClass = 'input-error alert-error';

    /**
     * Render a form row
     *
     * Skip form collection and fieldset
     *
     * {@inheritdoc}
     */
    public function render(ElementInterface $element)
    {
        /**#@+
         * Jump to specific collective renderer if specified
         */
        if ($element instanceof Collection) {
            return $this->view->formCollection($element);
        } elseif ($element instanceof FieldsetInterface) {
            return $this->view->formFieldset($element);
        }
        /**#@-*/

        /**#@+
         * Load description
         */
        $descriptionHelper  = $this->view->plugin('form_description');
        $elementDescription = $descriptionHelper->render($element);
        /**#@-*/

        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();

        $label           = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();

        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label, $this->getTranslatorTextDomain()
                );
            }
        }

        // Does this element have errors ?
        if (count($element->getMessages()) > 0 && !empty($inputErrorClass)) {
            $classAttributes = ($element->hasAttribute('class')
                ? $element->getAttribute('class') . ' ' : '');
            $classAttributes = $classAttributes . $inputErrorClass;

            $element->setAttribute('class', $classAttributes);
        }

        if ($this->partial) {
            $vars = array(
                'element'           => $element,
                'label'             => $label,
                'labelAttributes'   => $this->labelAttributes,
                'labelPosition'     => $this->labelPosition,
                'renderErrors'      => $this->renderErrors,
            );

            return $this->view->render($this->partial, $vars);
        }

        if ($this->renderErrors) {
            $elementErrors = $elementErrorsHelper->render($element);
        }

        $elementString = $elementHelper->render($element);

        if (isset($label) && '' !== $label) {
            $label = $escapeHtmlHelper($label);
            $labelAttributes = $element->getLabelAttributes();

            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }

            // Multicheckbox elements have to be handled differently
            // as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            $type = $element->getAttribute('type');
            if ($type === 'multi_checkbox'
                || $type === 'multicheckbox'
                || $type === 'radio'
            ) {
                $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s%s</fieldset>',
                    $label,
                    /**#@+
                     * For description
                     */
                    $elementDescription,
                    /**#@-*/
                    $elementString
                );
            } else {
                if ($element->hasAttribute('id')) {
                    $labelOpen = $labelHelper($element);
                    $labelClose = '';
                    $label = '';
                } else {
                    $labelOpen  = $labelHelper->openTag($labelAttributes);
                    $labelClose = $labelHelper->closeTag();
                }

                if ($label !== '' && !$element->hasAttribute('id')) {
                    $label = '<span>' . $label . '</span>';
                }

                // Button element is a special case,
                // because label is always rendered inside it
                if ($element instanceof Button) {
                    $labelOpen = $labelClose = $label = '';
                }

                switch ($this->labelPosition) {
                    case self::LABEL_PREPEND:
                        $markup = $labelOpen . $label . $elementString
                                . $labelClose;
                        break;
                    case self::LABEL_APPEND:
                    default:
                        $markup = $labelOpen . $elementString . $label
                                . $labelClose;
                        break;
                }
                /**#@+
                 * For description
                 */
                $markup = '<dt>' . $labelOpen . $label . $labelClose . '</dt>'
                        . $elementDescription . $elementString;
                /**#@-*/
            }

            if ($this->renderErrors) {
                $markup .= $elementErrors;
            }
        } else {
            /**#@+
             * For description
             */
            $elementString = $elementDescription . $elementString;
            /**#@-*/
            if ($this->renderErrors) {
                $markup = $elementString . $elementErrors;
            } else {
                $markup = $elementString;
            }
        }

        return $markup;
    }
}
