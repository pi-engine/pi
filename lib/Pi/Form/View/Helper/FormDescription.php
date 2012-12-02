<?php
/**
 * Form element description helper
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

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class FormDescription extends AbstractHelper
{
    /**
     * @var array Default attributes for the open format tag
     */
    protected $attributes = array();

    /**
     * Generate an opening text tag
     *
     * @param  null|array|ElementInterface $attributesOrElement
     * @return string
     */
    public function openTag($attributesOrElement = null)
    {
        if (is_array($attributesOrElement)) {
            $attributes = $this->createAttributesString($attributesOrElement);
        } elseif (is_string($attributesOrElement)) {
            $attributes = $attributesOrElement;
        }

        return !empty($attributes) ? sprintf('<span %s>', $attributes) : '<span>';
    }

    /**
     * Return a closing text tag
     *
     * @return string
     */
    public function closeTag()
    {
        return '</span>';
    }

    /**
     * Generate a form message, optionally with content
     *
     * Always generates a "for" statement, as we cannot assume the form input
     * will be provided in the $labelContent.
     *
     * @param  ElementInterface $element
     * @param  array $attributes
     * @return string|FormElementErrors
     */
    public function __invoke(ElementInterface $element = null, array $attributes = array())
    {
        if (!$element) {
            return $this;
        }
        return $this->render($element, $attributes);
    }

    /**
     * Utility form helper that renders a description
     *
     * @param  ElementInterface $element
     * @param  array $attributes
     * @return string
     */
    public function render(ElementInterface $element, array $attributes = array())
    {
        $message = $element->getAttribute('description');
        if (!$message) {
            return '';
        }
        // Prepare attributes for opening tag
        $attributes = array_merge($this->attributes, $attributes);

        $attributes = $this->createAttributesString($attributes);
        if (!empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

        return $this->openTag($attributes) . $message . $this->closeTag();
    }
}
