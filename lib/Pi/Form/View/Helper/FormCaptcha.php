<?php
/**
 * Form element CAPTCHA helper
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
use Zend\Captcha\AdapterInterface as CaptchaAdapter;

class FormCaptcha extends AbstractHelper
{
    /**
     * Render the captcha
     *
     * @param  ElementInterface $element
     * @param  array $options
     * @return string
     */
    public function render(ElementInterface $element, $options = array())
    {
        // To ensure CAPTCHA is initialized
        $captcha = $element->getCaptcha();

        if (!$captcha instanceof CaptchaAdapter) {
            throw new \DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute implementing Zend\Captcha\AdapterInterface; none found',
                __METHOD__
            ));
        }

        $helper  = $captcha->getHelperName();

        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            throw new \DomainException(sprintf(
                '%s requires that the renderer implements plugin(); it does not',
                __METHOD__
            ));
        }

        $helper = $renderer->plugin($helper);

        $separator = null;
        if (isset($options['separator'])) {
            $separator = $options['separator'];
        } elseif ($element->getOption('separator')) {
            $separator = $element->getOption('separator');
        }
        if (null !== $separator) {
            $helper->setSeparator($separator);
        }

        $position = null;
        if (isset($options['captcha_position'])) {
            $position = $options['captcha_position'];
        } elseif ($element->getOption('captcha_position')) {
            $position = $element->getOption('captcha_position');
        }
        if (null !== $position) {
            $helper->setCaptchaPosition($position);
        }

        return $helper($element);
    }

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  null|ElementInterface $element
     * @param  array $options
     * @return string|FormCaptcha
     */
    public function __invoke(ElementInterface $element = null, $options = array())
    {
        if (null === $element) {
            return $this;
        }
        return $this->render($element, $options);
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    public function setCaptchaPosition($position)
    {
        $this->captchaPosition = $position;
        return $this;
    }
}
