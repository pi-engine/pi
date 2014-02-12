<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form\View\Helper\Captcha;

use Zend\Form\View\Helper\Captcha\Image as ZendHelperCaptchaImage;
use Zend\Captcha\Image as CaptchaAdapter;
use Zend\Form\ElementInterface;

/**
 * CAPTCHA image helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Image extends ZendHelperCaptchaImage
{
    /**
     * Render the captcha
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" attribute'
                . ' of type Zend\Captcha\Image; none found',
                __METHOD__
            ));
        }

        $captcha->generate();

        $imgSrc = $captcha->getImgUrl() . '?id=' . $captcha->getId();
        $imgAttributes = array(
            'width'  => $captcha->getWidth(),
            'height' => $captcha->getHeight(),
            'alt'    => $captcha->getImgAlt(),
            //'src'    => $captcha->getImgUrl() . $captcha->getId()
            //. $captcha->getSuffix(),

            'src'    => $imgSrc,
            // For "click to refresh":
            // <img src="$src"
            //  onclick="this.src='$src&refresh='+Math.random()">
            'onclick'   => sprintf(
                'this.src=\'%s&refresh=\'+Math.random()',
                $imgSrc
            ),
            'style'     => 'cursor: pointer; vertical-align: middle;',
        );

        if ($element->hasAttribute('id')) {
            $imgAttributes['id'] = $element->getAttribute('id') . '-image';
        }

        $closingBracket = $this->getInlineClosingBracket();
        $img = sprintf(
            '<img %s%s',
            $this->createAttributesString($imgAttributes),
            $closingBracket
        );

        $position     = $this->getCaptchaPosition();
        $separator    = $this->getSeparator();
        $captchaInput = $this->renderCaptchaInputs($element);

        $pattern = '%s%s%s';
        if ($position == static::CAPTCHA_PREPEND) {
            return sprintf($pattern, $captchaInput, $separator, $img);
        }

        return sprintf($pattern, $img, $separator, $captchaInput);
    }
}
