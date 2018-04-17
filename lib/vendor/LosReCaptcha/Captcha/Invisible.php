<?php
namespace LosReCaptcha\Captcha;

use Traversable;
use LosReCaptcha\Service\ReCaptcha as ReCaptchaService;
use Zend\Stdlib\ArrayUtils;
use Zend\Captcha\AbstractAdapter;
use LosReCaptcha\Service\Exception;

/**
 * ReCaptcha v2 adapter
 *
 * Allows to insert captchas driven by ReCaptcha service
 *
 * @see http://recaptcha.net/apidocs/captcha/
 */
class Invisible extends ReCaptcha
{
    protected $responseField  = 'g-recaptcha-response';

    protected $service;
    protected $callback;
    protected $siteKey;
    protected $buttonId;

    /**#@+
     * Error codes
     */
    const MISSING_VALUE = 'missingValue';
    const ERR_CAPTCHA   = 'errCaptcha';
    const BAD_CAPTCHA   = 'badCaptcha';
    /**#@-*/

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::MISSING_VALUE => 'Missing captcha fields',
        self::ERR_CAPTCHA   => 'Failed to validate captcha',
        self::BAD_CAPTCHA   => 'Captcha value is wrong: %value%',
    ];

    /**
     * Constructor
     *
     * @param null|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if (! isset($options['site_key'])) {
            throw new Exception('Missing site key');
        }

        if (! isset($options['secret_key'])) {
            throw new Exception('Missing secret key');
        }

        if (! isset($options['callback'])) {
            throw new Exception('Missing callback function name');
        }

        if (! isset($options['button_id'])) {
            throw new Exception('Missing button_id');
        }

        $this->service = new ReCaptchaService($options['site_key'], $options['secret_key']);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        parent::__construct($options);

        $this->callback = $options['callback'];
        $this->buttonId = $options['button_id'];
        $this->siteKey = $options['site_key'];
    }

    public function getService()
    {
        return $this->service;
    }

    /**
     * @return mixed|string
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * @return mixed
     */
    public function siteKey()
    {
        return $this->siteKey;
    }

    /**
     * @return mixed|string
     */
    public function buttonId()
    {
        return $this->buttonId;
    }

    /**
     * Validate captcha
     *
     * @see \Zend\Validator\ValidatorInterface::isValid()
     * @param mixed $value
     * @param mixed $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if (empty($value)) {
            $this->error(self::MISSING_VALUE);
            return false;
        }

        $service = $this->getService();

        $res = $service->verify($value);
        if (! $res) {
            $this->error(self::ERR_CAPTCHA);
            return false;
        }

        if (! $res->isSuccess()) {
            $this->error(self::BAD_CAPTCHA);
            return false;
        }

        return true;
    }

    /**
     * Get helper name used to render captcha
     *
     * @return string
     */
    public function getHelperName()
    {
        /**
         * HACK FROM FREDERIC TISSOT / MARC DEROUSSEAUX
         */
        return \LosReCaptcha\Form\View\Helper\Captcha\Invisible::class;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Captcha\AdapterInterface::generate()
     */
    public function generate()
    {
        return '';
    }
}
