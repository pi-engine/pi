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

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;

/**
 * CAPTCHA element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormCaptcha extends AbstractHelper
{
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  null|ElementInterface $element
     * @param  array $options
     * @return string|self
     */
    public function __invoke(
        ElementInterface $element = null,
        $options = array()
    ) {
        if (null === $element) {
            return $this;
        }

        return $this->render($element, $options);
    }

    /**
     * Render the captcha
     *
     * @param  ElementInterface $element
     * @param  array $options
     * @return string
     */
    public function render(ElementInterface $element, $options = array())
    {
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new \DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute'
                . ' implementing Zend\Captcha\AdapterInterface; none found',
                __METHOD__
            ));
        }

        $helper  = $captcha->getHelperName();

        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            throw new \DomainException(sprintf(
                '%s requires that the renderer implements plugin();'
                . ' it does not',
                __METHOD__
            ));
        }

        $helper = $renderer->plugin($helper);

        // Custom CAPTCHA view
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
     * Set separator for display
     *
     * @see Pi\Form\View\Helper\Captcha\Image
     * @param string $separator
     * @return FormCaptcha
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Set CAPTCHA image display position
     *
     * @see Pi\Form\View\Helper\Captcha\Image
     * @param string $position
     * @return FormCaptcha
     */
    public function setCaptchaPosition($position)
    {
        $this->captchaPosition = $position;

        return $this;
    }
}
