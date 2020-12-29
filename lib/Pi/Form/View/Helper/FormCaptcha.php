<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

//use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Captcha\AdapterInterface as CaptchaAdapter;
use Laminas\Form\ElementInterface;

/**
 * CAPTCHA element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormCaptcha extends AbstractHelper
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element, $options = [])
    {
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new \DomainException(
                sprintf(
                    '%s requires that the element has a "captcha" attribute'
                    . ' implementing Laminas\Captcha\AdapterInterface; none found',
                    __METHOD__
                )
            );
        }

        $helper   = $captcha->getHelperName();
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            throw new \DomainException(
                sprintf(
                    '%s requires that the renderer implements plugin();'
                    . ' it does not',
                    __METHOD__
                )
            );
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
     * @param string $separator
     *
     * @return FormCaptcha
     * @see Pi\Form\View\Helper\Captcha\Image
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Set CAPTCHA image display position
     *
     * @param string $position
     *
     * @return FormCaptcha
     * @see Pi\Form\View\Helper\Captcha\Image
     */
    public function setCaptchaPosition($position)
    {
        $this->captchaPosition = $position;

        return $this;
    }
}
