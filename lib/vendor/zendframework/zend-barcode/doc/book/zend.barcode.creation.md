# Barcode creation using Zend\\Barcode\\Barcode class

## Using Zend\\Barcode\\Barcode::factory

`Zend\Barcode\Barcode` uses a factory method to create an instance of a renderer that extends
`Zend\Barcode\Renderer\AbstractRenderer`. The factory method accepts five arguments.

- The name of the barcode format (e.g., "code39") or a [Traversable](http://php.net/traversable)
object (required)
- The name of the renderer (e.g., "image") (required)
- Options to pass to the barcode object (an array or a [Traversable](http://php.net/traversable)
object) (optional)
- Options to pass to the renderer object (an array or a [Traversable](http://php.net/traversable)
object) (optional)
- Boolean to indicate whether or not to automatically render errors. If an exception occurs, the
provided barcode object will be replaced with an Error representation (optional default `TRUE`)

### Getting a Renderer with Zend\\Barcode\\Barcode::factory()

`Zend\Barcode\Barcode::factory()` instantiates barcode classes and renderers and ties them together.
In this first example, we will use the **Code39** barcode type together with the **Image** renderer.

```php
<?php
use Zend\Barcode\Barcode;

// Only the text to draw is required
$barcodeOptions = array('text' => 'ZEND-FRAMEWORK');

// No required options
$rendererOptions = array();
$renderer = Barcode::factory(
    'code39', 'image', $barcodeOptions, $rendererOptions
);

```

### Using Zend\\Barcode\\Barcode::factory() with Zend\\Config\\Config objects

You may pass a `Zend\Config\Config` object to the factory in order to create the necessary objects.
The following example is functionally equivalent to the previous.

```php
<?php
use Zend\Config\Config;
use Zend\Barcode\Barcode;

// Using only one Zend\Config\Config object
$config = new Config(array(
    'barcode'        => 'code39',
    'barcodeParams'  => array('text' => 'ZEND-FRAMEWORK'),
    'renderer'       => 'image',
    'rendererParams' => array('imageType' => 'gif'),
));

$renderer = Barcode::factory($config);

```

## Drawing a barcode

When you **draw** the barcode, you retrieve the resource in which the barcode is drawn. To draw a
barcode, you can call the `draw()` of the renderer, or simply use the proxy method provided by
`Zend\Barcode\Barcode`.

### Drawing a barcode with the renderer object

```php
<?php
use Zend\Barcode\Barcode;

// Only the text to draw is required
$barcodeOptions = array('text' => 'ZEND-FRAMEWORK');

// No required options
$rendererOptions = array();

// Draw the barcode in a new image,
$imageResource = Barcode::factory(
    'code39', 'image', $barcodeOptions, $rendererOptions
)->draw();

```

### Drawing a barcode with Zend\\Barcode\\Barcode::draw()

```php
<?php
use Zend\Barcode\Barcode;

// Only the text to draw is required
$barcodeOptions = array('text' => 'ZEND-FRAMEWORK');

// No required options
$rendererOptions = array();

// Draw the barcode in a new image,
$imageResource = Barcode::draw(
    'code39', 'image', $barcodeOptions, $rendererOptions
);

```

## Rendering a barcode

When you render a barcode, you draw the barcode, you send the headers and you send the resource
(e.g. to a browser). To render a barcode, you can call the `render()` method of the renderer or
simply use the proxy method provided by `Zend\Barcode\Barcode`.

### Rendering a barcode with the renderer object

```php
<?php
use Zend\Barcode\Barcode;

// Only the text to draw is required
$barcodeOptions = array('text' => 'ZEND-FRAMEWORK');

// No required options
$rendererOptions = array();

// Draw the barcode in a new image,
// send the headers and the image
Barcode::factory(
    'code39', 'image', $barcodeOptions, $rendererOptions
)->render();

```

This will generate this barcode:

![image](../images/zend.barcode.introduction.example-1.png)

### Rendering a barcode with Zend\\Barcode\\Barcode::render()

```php
<?php
use Zend\Barcode\Barcode;

// Only the text to draw is required
$barcodeOptions = array('text' => 'ZEND-FRAMEWORK');

// No required options
$rendererOptions = array();

// Draw the barcode in a new image,
// send the headers and the image
Barcode::render(
    'code39', 'image', $barcodeOptions, $rendererOptions
);

```

This will generate the same barcode as the previous example.

