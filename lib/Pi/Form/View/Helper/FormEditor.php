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

use Pi\Editor\Factory as EditorFactory;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
//use Zend\Form\View\Helper\AbstractHelper;

/**
 * Editor element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormEditor extends AbstractHelper
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element, $options = array())
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        $name   = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name;'
                . ' none discovered',
                __METHOD__
            ));
        }

        $options = array_replace($element->getOptions(), $options);
        $editorType = $element->getOption('editor') ?: 'textarea';
        $editor = EditorFactory::load($editorType, $options);

        $html = '';
        if ($editor) {
            $html = $editor->setView($renderer)->render($element);
        }

        if (!$html) {
            $html = $renderer->formTextarea($element);
        }

        return $html;
    }
}
