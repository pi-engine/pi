<?php
namespace LosReCaptcha\Service;

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
    protected $params = array(
        'ssl' => false, /* Use SSL or not when generating the recaptcha */
        'xhtml' => false /* Enable XHTML output (this will not be XHTML Strict
                            compliant since the IFRAME is necessary when
                            Javascript is disabled) */
    );

    /**
     * Options for tailoring reCaptcha
     *
     * See the different options on https://developers.google.com/recaptcha/docs/display#config
     *
     * @var array
     */
    protected $options = array(
        'theme' => 'light',
        'lang' => null, // Auto-detect
    );

    protected $request = null;

    /**
     * Class constructor
     *
     * @param string $siteKey
     * @param string $secretKey
     * @param array|Traversable $params
     * @param array|Traversable $options
     * @param string $ip
     */
    public function __construct($siteKey, $secretKey, RequestInterface $request = null, array $params = [], array $options = [], $ip = null)
    {
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
     * @return string
     * @throws \LosReCaptcha\Service\Exception
     */
    public function getHtml($name = null)
    {
        $host = self::API_SERVER;

        $langOption = '';

        if (isset($this->options['lang']) && !empty($this->options['lang'])) {
            $langOption = "?hl={$this->options['lang']}";
        }

        $return = <<<HTML
<div id="recaptcha_widget" class="g-recaptcha" data-sitekey="{$this->siteKey}" data-theme="{$this->options['theme']}"></div>
<noscript>
    <div style="width: 302px; height: 352px;">
        <div style="width: 302px; height: 352px; position: relative;">
            <div style="width: 302px; height: 352px; position: absolute;">
                <iframe src="{$host}/fallback?k={$this->siteKey}" frameborder="0" scrolling="no" style="width: 302px; height:352px; border-style: none;"></iframe>
            </div>
            <div style="width: 250px; height: 80px; position: absolute; border-style: none; bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
                <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 80px; border: 1px solid #c1c1c1; margin: 0px; padding: 0px; resize: none;" value=""></textarea>
            </div>
        </div>
    </div>
</noscript>
<script type="text/javascript" src="{$host}.js{$langOption}" async defer></script>
HTML;

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
