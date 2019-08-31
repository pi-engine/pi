# Custom LosReCaptcha

This library is a fork from https://github.com/Lansoweb/LosReCaptcha.
V2.1.0 is used.

All src files are push to root directory in order to be autoloaded correctly.
Now, this directory is loaded as standard library instead of ZF2 module.

Files modified :
vendor/LosReCaptcha/Captcha/ReCaptcha.php l.132 in order to return original class name, instead of ZF2 alias

Function is :

```
    public function getHelperName()
    {
        /**
         * HACK FROM FREDERIC TISSOT / MARC DEROUSSEAUX
         */
        return \LosReCaptcha\Form\View\Helper\Captcha\ReCaptcha::class;
    }
```

vendor/LosReCaptcha/Captcha/Invisible.php l.145 in order to return original class name, instead of ZF2 alias

Function is :

```
    public function getHelperName()
    {
        /**
         * HACK FROM FREDERIC TISSOT / MARC DEROUSSEAUX
         */
        return \LosReCaptcha\Form\View\Helper\Captcha\Invisible::class;
    }
```

vendor/LosReCaptcha/Form/View/Helper/Captcha/ReCaptcha.php
Added : captcha multi-instance (rework on captcha instantiation)
```
<script type="text/javascript" language="JavaScript">

$(window).on('load', function(){
    var hiddenElement = $('#$responseId-$uniqueId');
    var form = hiddenElement.parents('form');
    
    form.submit(function(){
        var responseValue = form.find('.g-recaptcha-response').val();
        hiddenElement.val(responseValue);
    });
});
</script>
```

Added : unique identifier for each form on the current page (with uniqueId variable)
Fixed : compatibility from jQuery 1.x to 3.x (change "load" method to "on" method)