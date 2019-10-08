# Zend\\Barcode Renderers

Renderers have some common options. These options can be set in three ways:

- As an array or a [Traversable](http://php.net/traversable) object passed to the constructor.
- As an array passed to the `setOptions()` method.
- As discrete values passed to individual setters.

### Different ways to parameterize a renderer object

```php
<?php
use Zend\Barcode\Renderer;

$options = array('topOffset' => 10);

// Case 1
$renderer = new Renderer\Pdf($options);

// Case 2
$renderer = new Renderer\Pdf();
$renderer->setOptions($options);

// Case 3
$renderer = new Renderer\Pdf();
$renderer->setTopOffset(10);

```

## Common Options

In the following list, the values have no unit; we will use the term "unit." For example, the
default value of the "thin bar" is "1 unit." The real units depend on the rendering support. The
individual setters are obtained by uppercasing the initial letter of the option and prefixing the
name with "set" (e.g. "barHeight" =\> "setBarHeight"). All options have a correspondent getter
prefixed with "get" (e.g. "getBarHeight"). Available options are:


|Option              |Data Type            |Default Value          |Description                                                                                                                                                                                                 |
|--------------------|---------------------|-----------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|rendererNamespace   |String               |Zend\\Barcode\\Renderer|Namespace of the renderer; for example, if you need to extend the renderers                                                                                                                                 |
|horizontalPosition  |String               |"left"                 |Can be "left", "center" or "right". Can be useful with PDF or if the setWidth() method is used with an image renderer.                                                                                      |
|verticalPosition    |String               |"top"                  |Can be "top", "middle" or "bottom". Can be useful with PDF or if the setHeight() method is used with an image renderer.                                                                                     |
|leftOffset          |Integer              |0                      |Top position of the barcode inside the renderer. If used, this value will override the "horizontalPosition" option.                                                                                         |
|topOffset           |Integer              |0                      |Top position of the barcode inside the renderer. If used, this value will override the "verticalPosition" option.                                                                                           |
|automaticRenderError|Boolean              |FALSE                  |Whether or not to automatically render errors. If an exception occurs, the provided barcode object will be replaced with an Error representation. Note that some errors (or exceptions) can not be rendered.|
|moduleSize          |Float                |1                      |Size of a rendering module in the support.                                                                                                                                                                  |
|barcode             |Zend\\Barcode\\Object|NULL                   |The barcode object to render.                                                                                                                                                                               |

 An additional getter
exists: `getType()`. It returns the name of the renderer class without the namespace (e.g.
`Zend\Barcode\Renderer\Image` returns "image").

## Zend\\Barcode\\Renderer\\Image

The Image renderer will draw the instruction list of the barcode object in an image resource. The
component requires the GD extension. The default width of a module is 1 pixel.

Available options are:


|Option   |Data Type|Default Value|Description                                                                                                      |
|---------|---------|-------------|-----------------------------------------------------------------------------------------------------------------|
|height   |Integer  |0            |Allow you to specify the height of the result image. If "0", the height will be calculated by the barcode object.|
|width    |Integer  |0            |Allow you to specify the width of the result image. If "0", the width will be calculated by the barcode object.  |
|imageType|String   |"png"        |Specify the image format. Can be "png", "jpeg", "jpg" or "gif".                                                  |



## Zend\\Barcode\\Renderer\\Pdf

The *PDF* renderer will draw the instruction list of the barcode object in a *PDF* document. The
default width of a module is 0.5 point.

There are no particular options for this renderer.

