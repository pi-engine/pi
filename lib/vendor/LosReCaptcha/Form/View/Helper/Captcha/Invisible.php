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

class Invisible extends FormInput
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
        if (! $element) {
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

        if ($captcha === null || ! $captcha instanceof CaptchaAdapter) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has a "captcha" is an instance of LosReCaptcha\Captcha\ReCaptcha',
                __METHOD__
            ));
        }

        $name   = $element->getName();
        $id     = isset($attributes['id']) ? $attributes['id'] : $name;
        $responseName  = empty($name) ? 'recaptcha_response_field' : $name . '[recaptcha_response_field]';
        $responseId    = $id . '-response';

        $uniqueId = rand();

        $markup = $captcha->getService()->getInvisibleHtml($uniqueId);
        $hidden = $this->renderHiddenInput($responseName, $responseId, $uniqueId);
        $js     = $this->renderJsEvents($responseId, $captcha->siteKey(), $captcha->buttonId() . '-' . $uniqueId, $captcha->callback() . '_' . $uniqueId, $uniqueId);

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
    private function renderHiddenInput($responseName, $responseId, $uniqueId)
    {
        $pattern        = '<input type="hidden" %s%s';
        $closingBracket = $this->getInlineClosingBracket();

        $attributes = $this->createAttributesString([
            'name' => $responseName,
            'id'   => $responseId . '-' . $uniqueId,
        ]);
        $response = sprintf($pattern, $attributes, $closingBracket);

        return $response;
    }

    /**
     * Create the JS events used to bind the response value to the submitted form.
     * @param $responseId
     * @param $siteKey
     * @param $buttonId
     * @param $callback
     * @param $uniqueId
     * @return string
     */
    private function renderJsEvents($responseId, $siteKey, $buttonId, $callback, $uniqueId)
    {
        $js = <<<EOJ
<script type="text/javascript" language="JavaScript">
function renderLosInvisibleRecaptcha_$uniqueId() {
  var submitElement = $('#$responseId-$uniqueId').closest('form').find('button[name="submit-button"]');
  submitElement.attr('id', 'submit-button-$uniqueId');
  
  grecaptcha.render('$buttonId', {
    'sitekey' : '$siteKey',
    'callback' : losInvisibleRecaptchaCallback_$uniqueId
  });
};
function losInvisibleRecaptchaCallback_$uniqueId(token) {
  document.getElementById("$responseId-$uniqueId").value = token;
  {$callback}();
};

function captchaSubmit_$uniqueId() {
  // Any js code, eg. fields validation
  console.log('test');
  
  $('#$responseId-$uniqueId').parents('form').submit();
}
</script>
EOJ;
        return $js;
    }
}
