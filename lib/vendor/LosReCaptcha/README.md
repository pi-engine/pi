# LosReCaptcha

PHP module for using the ReCaptcha v2 system from Google

[https://www.google.com/recaptcha/intro/index.html](https://www.google.com/recaptcha/intro/index.html)

## Zend Form

To use with Zend\Form, just initialize like the default ReCaptcha element:
```php
$this->add([
   'name' => 'captcha',
    'type' => 'captcha',
    'options' => [
        'captcha' => new LosReCaptcha\Captcha\ReCaptcha([
            'site_key' => $siteKey,
            'secret_key' => $siteSecret,
        ]),
    ],
]);
```

You need to add the necessary alias to your view_helper instance:
```php
return [
	'view_helpers' {
	    'aliases' => [
	        'losrecaptcha/recaptcha'  => LosReCaptcha\Form\View\Helper\Captcha\ReCaptcha::class,
	    ],
	    'factories' => [
	        LosReCaptcha\Form\View\Helper\Captcha\ReCaptcha::class => 
	            Zend\ServiceManager\Factory\InvokableFactory::class,
	    ],
    ],
];
```

For Zend Expressive, you can inject the configuration with the ConfigProvider:
```php
<?php
namespace App\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Config;
use Zend\View\HelperPluginManager;
use Zend\Form\ConfigProvider as FormConfigProvider;
use Zend\I18n\ConfigProvider as I18nConfigProvider;
use LosReCaptcha\ConfigProvider as CaptchaConfigProvider;

class HelperPluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['view_helpers']) ? $config['view_helpers'] : [];
        $manager = new HelperPluginManager($container);

        $manager->configure((new FormConfigProvider())->getViewHelperConfig());
        $manager->configure((new I18nConfigProvider())->getViewHelperConfig());
        $manager->configure((new CaptchaConfigProvider())->getViewHelperConfig());
        $manager->configure($config);

        return $manager;
    }
}
```

And to use the factory, add the following to your dependencies:
```php
'dependencies' => [
        'factories' => [
            Zend\View\HelperPluginManager::class =>
                App\View\Helper\Factory\HelperPluginManagerFactory::class,
        ],
    ],
```
