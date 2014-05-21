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

//use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

/**
 * Description helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
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

        return !empty($attributes)
            ? sprintf('<span %s>', $attributes) : '<span>';
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
     * {@inheritDoc}
     */
    public function render(
        ElementInterface $element,
        $attributes = array()
     ) {
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
