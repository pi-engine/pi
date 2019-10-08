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

For [Invisible ReCaptcha](https://developers.google.com/recaptcha/docs/invisible):
```php
// ...
$this->add([
   'name' => 'captcha',
    'type' => 'captcha',
    'options' => [
        'captcha' => new \LosReCaptcha\Captcha\Invisible([
            'site_key' => $siteKey,
            'secret_key' => $siteSecret,
            'callback' => 'captchaSubmit', // Callback to submit the form
            'button_id' => 'submit-button', // Button id to submit the form
        ]),
    ],
]);
// ...
$this->add([
    'name' => 'submit-button',
    'type' => \Zend\Form\Element\Button::class,
    'options' => [
        'label' => _('Log In'),
    ],
    'attributes' => [
        'id'    => 'submit-button',
        'class' => 'btn btn-block btn-primary',
        'value' => _('Log In'),
    ],
]);
```

In the view for Invisible ReCaptcha:
```html
function captchaSubmit() {
  // Any js code, eg. fields validation
  document.getElementById("login").submit();
}
```

For Zend Expressive, you can inject the configuration with the ConfigProvider inside your config/config.php:
```php
<?php
// ...
$aggregator = new ConfigAggregator([
    // ...
    \LosReCaptcha\ConfigProvider::class,
    // ...
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
```
