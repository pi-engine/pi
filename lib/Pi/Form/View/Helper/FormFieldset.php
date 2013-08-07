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

use Zend\Form\FieldsetInterface;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\AbstractHelper;

/**
 * Fieldset helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormFieldset extends AbstractHelper
{
    /** @var FormRow Row render helper */
    protected $rowHelper;

    /**
     * If set to true, collections are automatically wrapped around a fieldset
     *
     * @var bool
     */
    protected $shouldWrap = true;

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @param  bool $wrap
     * @return string|self
     */
    public function __invoke(ElementInterface $element = null, $wrap = true)
    {
        if (!$element) {
            return $this;
        }

        $this->setShouldWrap($wrap);

        return $this->render($element);
    }

    /**
     * If set to true, collections are automatically wrapped around a fieldset
     *
     * @param bool $wrap
     * @return self
     */
    public function setShouldWrap($wrap)
    {
        $this->shouldWrap = (bool) $wrap;

        return $this;
    }

    /**
     * Get wrapped
     *
     * @return bool
     */
    public function shouldWrap()
    {
        return $this->shouldWrap();
    }

    /**
     * Retrieve the FormRow helper
     *
     * @return FormRow
     */
    protected function getRowHelper()
    {
        if ($this->rowHelper) {
            return $this->rowHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->rowHelper = $this->view->plugin('form_row');
        }

        return $this->rowHelper;
    }

    /**
     * Render a fieldset by iterating through all fieldsets and elements
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        $markup = '';
        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $rowHelper = $this->getRowHelper();

        foreach($element->getIterator() as $elementOrFieldset) {
            if ($elementOrFieldset instanceof FieldsetInterface) {
                $markup .= $this->render($elementOrFieldset);
            } elseif ($elementOrFieldset instanceof ElementInterface) {
                $markup .= $rowHelper($elementOrFieldset);
            }
        }

        // Every collection is wrapped by a fieldset if needed
        if ($this->shouldWrap) {
            $label = $element->getLabel();

            if (!empty($label)) {
                $label = $escapeHtmlHelper($label);

                $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s</fieldset>',
                    $label,
                    $markup
                );
            }
        }

        return $markup;
    }
}
