<?php
namespace LosReCaptcha\Service;

use Pi;
use Traversable;
use LosReCaptcha\Service\Request\RequestInterface;
use LosReCaptcha\Service\Request\Curl;
use LosReCaptcha\Service\Request\Parameters;

class ReCaptcha
{
    /**
     * URI to the regular API
     *
     * @var string
     */
    const API_SERVER = 'https://www.google.com/recaptcha/api';

    /**
     * URI to the verify server
     *
     * @var string
     */
    const VERIFY_SERVER = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Site key used when displaying the captcha
     *
     * @var string
     */
    protected $siteKey = null;

    /**
     * Secret key used when verifying user input
     *
     * @var string
     */
    protected $secretKey = null;

    /**
     * Ip address used when verifying user input
     *
     * @var string
     */
    protected $ip = null;

    /**
     * Parameters for the object
     *
     * @var array
     */
    protected $params = [
        'ssl' => false, /* Use SSL or not when generating the recaptcha */
        'xhtml' => false /* Enable XHTML output (this will not be XHTML Strict
                            compliant since the IFRAME is necessary when
                            Javascript is disabled) */
    ];

    /**
     * Options for tailoring reCaptcha
     *
     * See the different options on https://developers.google.com/recaptcha/docs/display#config
     *
     * @var array
     */
    protected $options = [
        'theme' => 'light',
        'lang' => null, // Auto-detect
    ];

    protected $request = null;

    /**
     * Class constructor
     *
     * @param string $siteKey
     * @param string $secretKey
     * @param array $params
     * @param array $options
     * @param string $ip
     */
    public function __construct(
        $siteKey,
        $secretKey,
        RequestInterface $request = null,
        array $params = [],
        array $options = [],
        $ip = null
    ) {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;

        if ($request == null) {
            $request = new Curl();
        }
        $this->request = $request;

        if ($ip !== null) {
            $this->ip = $ip;
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        $this->params = $params;

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Serialize as string
     *
     * When the instance is used as a string it will display the recaptcha.
     * Since we can't throw exceptions within this method we will trigger
     * a user warning instead.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->getHtml();
        } catch (\Exception $e) {
            $return = '';
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return $return;
    }

    /**
     * Get the HTML code for the captcha
     *
     * This method uses the public key to fetch a recaptcha form.
     *
     * @param null|string $name Base name for recaptcha form elements
     * @param string $uniqueId
     * @return string
     * @throws \LosReCaptcha\Service\Exception
     */
    public function getHtml($name = null, $uniqueId)
    {
        $host = self::API_SERVER;

        $langOption = '';

        $return = <<<HTML
<div id="recaptcha_widget_$uniqueId" class="g-recaptcha" data-sitekey="{$this->siteKey}" data-theme="{$this->options['theme']}"></div>
HTML;

        if(empty($GLOBALS['recaptchaScriptLoaded'])){
            $GLOBALS['recaptchaScriptLoaded'] = true;

            $script = <<<JS
var onloadCallback = function() {
    
    var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    
    var size = (w <= 479) ? 'compact':'normal';
    
    $('.g-recaptcha').each(function(){
        var elementId = $(this).attr('id');
        grecaptcha.render(document.getElementById(elementId), {
            'sitekey' : '{$this->siteKey}',
            'size' : size,
        });
    });
};
JS;


            Pi::service('view')->getHelper('footScript')->appendScript($script);

            $action = Pi::engine()->application()->getRouteMatch()->getParam('action', 'index');

            if($action == 'modal'){
                $options = array();
            } else {
                $options = array('async' => 'async', 'defer' => true);
            }
            Pi::service('view')->getHelper('footScript')->appendFile($host . '.js?onload=onloadCallback&render=explicit', 'text/javascript', $options);

        }

        return $return;
    }

    public function getInvisibleHtml($uniqueId)
    {
        $host = self::API_SERVER;

        $return = <<<HTML
<div id="recaptcha_widget_$uniqueId" class="g-recaptcha" data-sitekey="{$this->siteKey}" data-size="invisible"></div>
HTML;


        if(empty($GLOBALS['recaptchaScriptLoaded'])){
            $GLOBALS['recaptchaScriptLoaded'] = true;

            Pi::service('view')->getHelper('footScript')->appendFile($host . '.js?onload=renderLosInvisibleRecaptcha_'.$uniqueId.'&render=explicit', 'text/javascript', array('async' => 'async', 'defer' => true));

        }


        return $return;
    }

    /**
     * Gets a solution to the verify server
     *
     * @param string $responseField
     * @return \LosReCaptcha\Service\Response
     * @throws \LosReCaptcha\\Service\Exception
     */
    protected function query($responseField)
    {
        $params = new Parameters($this->secretKey, $responseField, $this->ip);

        return $this->request->send($params);
    }

    /**
     * Verify the user input
     *
     * This method calls up the post method and returns a
     * Zend_Service_ReCaptcha_Response object.
     *
     * @param string $responseField
     * @return \LosReCaptcha\Service\Response
     */
    public function verify($responseField)
    {
        $response = $this->query($responseField);
        return Response::fromJson($response);
    }
}
