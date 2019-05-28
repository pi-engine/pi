# Captcha Operation

## The AdapterInterface

All *CAPTCHA* adapters implement `Zend\Captcha\AdapterInterface`, which looks like the following:

``` sourceCode
namespace Zend\Captcha;

use Zend\Validator\ValidatorInterface;

interface AdapterInterface extends ValidatorInterface
{
    public function generate();

    public function setName($name);

    public function getName();

    // Get helper name used for rendering this captcha type
    public function getHelperName();
}
```

The name setter and getter are used to specify and retrieve the *CAPTCHA* identifier. The most
interesting methods are `generate()` and `render()`. `generate()` is used to create the *CAPTCHA*
token. This process typically will store the token in the session so that you may compare against it
in subsequent requests. `render()` is used to render the information that represents the *CAPTCHA*,
be it an image, a figlet, a logic problem, or some other *CAPTCHA*.

## Basic Usage

A simple use case might look like the following:

``` sourceCode
// Originating request:
$captcha = new Zend\Captcha\Figlet(array(
    'name' => 'foo',
    'wordLen' => 6,
    'timeout' => 300,
));

$id = $captcha->generate();

//this will output a Figlet string
echo $captcha->getFiglet()->render($captcha->getWord());


// On a subsequent request:
// Assume a captcha setup as before, with corresponding form fields, the value of $_POST['foo']
// would be key/value array: id => captcha ID, input => captcha value
if ($captcha->isValid($_POST['foo'], $_POST)) {
    // Validated!
}
```

> ## Note
Under most circumstances, you probably prefer the use of `Zend\Captcha` functionality combined with
the power of the `Zend\Form` component. For an example on how to use `Zend\Form\Element\Captcha`,
have a look at the \[Zend\\Form Quick Start\](zend.form.quick-start).
