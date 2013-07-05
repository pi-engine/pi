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
 * @package         Pi\Form
 * @subpackage      View
 */

namespace Pi\Form\View\Helper;

use Zend\Form\View\Helper\FormElement as ZendFormElement;
use Zend\Form\ElementInterface;

class FormElement extends ZendFormElement
{
    /**
     * {@inheritdoc}
     */
    public function render(ElementInterface $element)
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        $type   = $element->getAttribute('type');
        if ($type) {
            $type = sprintf('form_%s', str_replace('-', '_', $type));
            $helper = $renderer->plugin($type);
            if ($helper) {
                return $helper($element);
            }
        }

        return parent::render($element);
    }
}
