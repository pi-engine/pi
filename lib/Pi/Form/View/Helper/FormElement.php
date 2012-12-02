<?php
/**
 * Form element view helper
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

use Zend\Form\Element;
use Zend\Form\View\Helper\FormElement as ZendFormElement;
use Zend\Form\ElementInterface;

class FormElement extends ZendFormElement
{
    /**
     * Render an element
     *
     * Introspects the element type and attributes to determine which
     * helper to utilize when rendering.
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

        if ($element instanceof Element\Captcha) {
            $helper = $renderer->plugin('form_captcha');
            return $helper($element);
        }

        /*
        if ($element instanceof Element\Csrf) {
            $helper = $renderer->plugin('form_hidden');
            return $helper($element);
        }
        */

        if ($element instanceof Element\Collection) {
            $helper = $renderer->plugin('form_collection');
            return $helper($element);
        }

        $type   = $element->getAttribute('type') ?: 'input';
        $type = sprintf('form_%s', $type);
        $helper = $renderer->plugin($type);
        if ($helper) {
            return $helper($element);
        }

        $helper = $renderer->plugin('form_input');
        return $helper($element);
    }
}
