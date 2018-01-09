<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace LosReCaptcha\Form\View\Helper\Captcha;

use LosReCaptcha\Captcha\ReCaptcha as CaptchaAdapter;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormInput;

class ReCaptcha extends FormInput
{
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render ReCaptcha form elements
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $attributes = $element->getAttributes();
        $captcha = $element->getCaptcha();

        if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" is an instance of LosReCaptcha\Captcha\ReCaptcha',
                __METHOD__
            ));
        }

        $name          = $element->getName();
        $id            = isset($attributes['id']) ? $attributes['id'] : $name;
        $responseName  = empty($name) ? 'recaptcha_response_field'  : $name . '[recaptcha_response_field]';
        $responseId    = $id . '-response';

        $uniqueId = rand();

        $markup = $captcha->getService()->getHtml($name, $uniqueId);
        $hidden = $this->renderHiddenInput($responseName, $responseId, $uniqueId);
        $js     = $this->renderJsEvents($responseId, $uniqueId);

        return $hidden . $markup . $js;
    }

    /**
     * Render hidden input elements for the response
     *
     * @param  string $responseName
     * @param  string $responseId
     * @param  string $uniqueId
     * @return string
     */
    protected function renderHiddenInput($responseName, $responseId, $uniqueId)
    {
        $pattern        = '<input type="hidden" %s%s';
        $closingBracket = $this->getInlineClosingBracket();

        $attributes = $this->createAttributesString(array(
            'name' => $responseName,
            'id'   => $responseId . '-' . $uniqueId,
        ));
        $response = sprintf($pattern, $attributes, $closingBracket);

        return $response;
    }

    /**
     * Create the JS events used to bind the response value to the submitted form.
     *
     * @param  string $responseId
     * @param  string $uniqueId
     * @return string
     */
    protected function renderJsEvents($responseId, $uniqueId)
    {
        $js =<<<EOJ
<script>

$(window).on('load', function(){
    var hiddenElement = $('#$responseId-$uniqueId');
    var form = hiddenElement.parents('form');
    
    form.submit(function(){
        var responseValue = form.find('.g-recaptcha-response').val();
        hiddenElement.val(responseValue);
    });
});
</script>
EOJ;
        return $js;
    }
}
