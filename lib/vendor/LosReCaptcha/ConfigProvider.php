<?php
namespace LosReCaptcha;

use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * Return general-purpose zend-i18n configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'view_helpers' => $this->getViewHelperConfig(),
        ];
    }

    /**
     * Return zend-view helper configuration.
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'aliases' => [
                'losrecaptcha/recaptcha'  => Form\View\Helper\Captcha\ReCaptcha::class,
            ],
            'factories' => [
                Form\View\Helper\Captcha\ReCaptcha::class => InvokableFactory::class,
            ],
        ];
    }
}
