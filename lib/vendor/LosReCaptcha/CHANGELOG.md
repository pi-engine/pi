# Custom LosReCaptcha

This library is a fork from https://packagist.org/packages/los/losrecaptcha.
All src files are push to root directory in order to be autoloaded correctly.
Now, this directory is loaded as standard library instead of ZF2 module.

Only one file has been modified :
vendor/LosReCaptcha/Captcha/ReCaptcha.php l.132 in order to return original class name, instead of ZF2 alias
