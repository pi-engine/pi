# Zend\\Barcode Objects

Barcode objects allow you to generate barcodes independently of the rendering support. After
generation, you can retrieve the barcode as an array of drawing instructions that you can provide to
a renderer.

Objects have a large number of options. Most of them are common to all objects. These options can be
set in three ways:

- As an array or a [Traversable](http://php.net/traversable) object passed to the constructor.
- As an array passed to the `setOptions()` method.
- Via individual setters for each configuration type.

### Different ways to parameterize a barcode object

```php
<?php
use Zend\Barcode\Object;

$options = array('text' => 'ZEND-FRAMEWORK', 'barHeight' => 40);

// Case 1: constructor
$barcode = new Object\Code39($options);

// Case 2: setOptions()
$barcode = new Object\Code39();
$barcode->setOptions($options);

// Case 3: individual setters
$barcode = new Object\Code39();
$barcode->setText('ZEND-FRAMEWORK')
        ->setBarHeight(40);

```

## Common Options

In the following list, the values have no units; we will use the term "unit." For example, the
default value of the "thin bar" is "1 unit". The real units depend on the rendering support (see the
renderers documentation
\<zend.barcode.renderers\> for more information). Setters are each named by uppercasing the initial
letter of the option and prefixing the name with "set" (e.g. "barHeight" becomes "setBarHeight").
All options have a corresponding getter prefixed with "get" (e.g. "getBarHeight"). Available options
are:


|Option            |Data Type                        |Default Value        |Description                                                                                                |
|------------------|---------------------------------|---------------------|-----------------------------------------------------------------------------------------------------------|
|barcodeNamespace  |String                           |Zend\\Barcode\\Object|Namespace of the barcode; for example, if you need to extend the embedding objects                         |
|barHeight         |Integer                          |50                   |Height of the bars                                                                                         |
|barThickWidth     |Integer                          |3                    |Width of the thick bar                                                                                     |
|barThinWidth      |Integer                          |1                    |Width of the thin bar                                                                                      |
|factor            |Integer, Float, String or Boolean|1                    |Factor by which to multiply bar widths and font sizes (barHeight, barThinWidth, barThickWidth and fontSize)|
|foreColor         |Integer                          |0x000000 (black)     |Color of the bar and the text. Could be provided as an integer or as a HTML value (e.g. "#333333")         |
|backgroundColor   |Integer or String                |0xFFFFFF (white)     |Color of the background. Could be provided as an integer or as a HTML value (e.g. "#333333")               |
|orientation       |Integer, Float, String or Boolean|0                    |Orientation of the barcode                                                                                 |
|font              |String or Integer                |NULL                 |Font path to a TTF font or a number between 1 and 5 if using image generation with GD (internal fonts)     |
|fontSize          |Float                            |10                   |Size of the font (not applicable with numeric fonts)                                                       |
|withBorder        |Boolean                          |FALSE                |Draw a border around the barcode and the quiet zones                                                       |
|withQuietZones    |Boolean                          |TRUE                 |Leave a quiet zone before and after the barcode                                                            |
|drawText          |Boolean                          |TRUE                 |Set if the text is displayed below the barcode                                                             |
|stretchText       |Boolean                          |FALSE                |Specify if the text is stretched all along the barcode                                                     |
|withChecksum      |Boolean                          |FALSE                |Indicate whether or not the checksum is automatically added to the barcode                                 |
|withChecksumInText|Boolean                          |FALSE                |Indicate whether or not the checksum is displayed in the textual representation                            |
|text              |String                           |NULL                 |The text to represent as a barcode                                                                         |



### Particular case of static setBarcodeFont()

You can set a common font for all your objects by using the static method
`Zend\Barcode\Barcode::setBarcodeFont()`. This value can be always be overridden for individual
objects by using the `setFont()` method.

```php
<?php
use Zend\Barcode\Barcode;

// In your bootstrap:
Barcode::setBarcodeFont('my_font.ttf');

// Later in your code:
Barcode::render(
    'code39',
    'pdf',
    array('text' => 'ZEND-FRAMEWORK')
); // will use 'my_font.ttf'

// or:
Barcode::render(
    'code39',
    'image',
    array(
        'text' => 'ZEND-FRAMEWORK',
        'font' => 3
    )
); // will use the 3rd GD internal font

```

## Common Additional Getters


|Getter                             |Data Type|Description                                                                                                            |
|-----------------------------------|---------|-----------------------------------------------------------------------------------------------------------------------|
|getType()                          |String   |Return the name of the barcode class without the namespace (e.g. Zend\\Barcode\\Object\\Code39 returns simply "code39")|
|getRawText()                       |String   |Return the original text provided to the object                                                                        |
|getTextToDisplay()                 |String   |Return the text to display, including, if activated, the checksum value                                                |
|getQuietZone()                     |Integer  |Return the size of the space needed before and after the barcode without any drawing                                   |
|getInstructions()                  |Array    |Return drawing instructions as an array.                                                                               |
|getHeight($recalculate = false)    |Integer  |Return the height of the barcode calculated after possible rotation                                                    |
|getWidth($recalculate = false)     |Integer  |Return the width of the barcode calculated after possible rotation                                                     |
|getOffsetTop($recalculate = false) |Integer  |Return the position of the top of the barcode calculated after possible rotation                                       |
|getOffsetLeft($recalculate = false)|Integer  |Return the position of the left of the barcode calculated after possible rotation                                      |




## Description of shipped barcodes

You will find below detailed information about all barcode types shipped by default with Zend
Framework.

### Zend\\Barcode\\Object\\Error

![image](../images/zend.barcode.objects.details.error.png)

This barcode is a special case. It is internally used to automatically render an exception caught by
the `Zend\Barcode` component.

### Zend\\Barcode\\Object\\Code128

![image](../images/zend.barcode.objects.details.code128.png)

- **Name:** Code 128
- **Allowed characters:** the complete ASCII-character set
- **Checksum:** optional (modulo 103)
- **Length:** variable

There are no particular options for this barcode.

### Zend\\Barcode\\Object\\Codabar

![image](../images/zend.barcode.objects.details.codabar.png)

- **Name:** Codabar (or Code 2 of 7)
- **Allowed characters:**'0123456789-\$:/.+' with 'ABCD' as start and stop characters
- **Checksum:** none
- **Length:** variable

There are no particular options for this barcode.

### Zend\\Barcode\\Object\\Code25

![image](../images/zend.barcode.objects.details.code25.png)

- **Name:** Code 25 (or Code 2 of 5 or Code 25 Industrial)
- **Allowed characters:**'0123456789'
- **Checksum:** optional (modulo 10)
- **Length:** variable

There are no particular options for this barcode.

### Zend\\Barcode\\Object\\Code25interleaved

![image](../images/zend.barcode.objects.details.int25.png)

This barcode extends `Zend\Barcode\Object\Code25` (Code 2 of 5), and has the same particulars and
options, and adds the following:

- **Name:** Code 2 of 5 Interleaved
- **Allowed characters:**'0123456789'
- **Checksum:** optional (modulo 10)
- **Length:** variable (always even number of characters)

Available options include:


|Option        |Data Type|Default Value|Description                                               |
|--------------|---------|-------------|----------------------------------------------------------|
|withBearerBars|Boolean  |FALSE        |Draw a thick bar at the top and the bottom of the barcode.|


> If the number of characters is not even, ``Zend\Barcode\Object\Code25interleaved`` will automatically 
prepend the missing zero to the barcode text.

### Zend\\Barcode\\Object\\Ean2

![image](../images/zend.barcode.objects.details.ean2.png)

This barcode extends `Zend\Barcode\Object\Ean5` (*EAN* 5), and has the same particulars and options,
and adds the following:

- **Name:** *EAN*-2
- **Allowed characters:**'0123456789'
- **Checksum:** only use internally but not displayed
- **Length:** 2 characters

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 2, `Zend\Barcode\Object\Ean2` will automatically prepend
the missing zero to the barcode text.

### Zend\\Barcode\\Object\\Ean5

![image](../images/zend.barcode.objects.details.ean5.png)

This barcode extends `Zend\Barcode\Object\Ean13` (*EAN* 13), and has the same particulars and
options, and adds the following:

- **Name:** *EAN*-5
- **Allowed characters:**'0123456789'
- **Checksum:** only use internally but not displayed
- **Length:** 5 characters

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 5, `Zend\Barcode\Object\Ean5` will automatically prepend
the missing zero to the barcode text.

### Zend\\Barcode\\Object\\Ean8

![image](../images/zend.barcode.objects.details.ean8.png)

This barcode extends `Zend\Barcode\Object\Ean13` (*EAN* 13), and has the same particulars and
options, and adds the following:

- **Name:** *EAN*-8
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10)
- **Length:** 8 characters (including checksum)

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 8, `Zend\Barcode\Object\Ean8` will automatically prepend
the missing zero to the barcode text.

### Zend\\Barcode\\Object\\Ean13

![image](../images/zend.barcode.objects.details.ean13.png)

- **Name:** *EAN*-13
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10)
- **Length:** 13 characters (including checksum)

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 13, `Zend\Barcode\Object\Ean13` will automatically prepend
the missing zero to the barcode text.
The option `withQuietZones` has no effect with this barcode.

### Zend\\Barcode\\Object\\Code39

![image](../images/zend.barcode.introduction.example-1.png)

- **Name:** Code 39
- **Allowed characters:**'0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ -.\$/+%'
- **Checksum:** optional (modulo 43)
- **Length:** variable

> ## Note
`Zend\Barcode\Object\Code39` will automatically add the start and stop characters ('\*') for you.

There are no particular options for this barcode.

### Zend\\Barcode\\Object\\Identcode

![image](../images/zend.barcode.objects.details.identcode.png)

This barcode extends `Zend\Barcode\Object\Code25interleaved` (Code 2 of 5 Interleaved), and inherits
some of its capabilities; it also has a few particulars of its own.

- **Name:** Identcode (Deutsche Post Identcode)
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10 different from Code25)
- **Length:** 12 characters (including checksum)

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 12, `Zend\Barcode\Object\Identcode` will automatically
prepend missing zeros to the barcode text.

### Zend\\Barcode\\Object\\Itf14

![image](../images/zend.barcode.objects.details.itf14.png)

This barcode extends `Zend\Barcode\Object\Code25interleaved` (Code 2 of 5 Interleaved), and inherits
some of its capabilities; it also has a few particulars of its own.

- **Name:** *ITF*-14
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10)
- **Length:** 14 characters (including checksum)

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 14, `Zend\Barcode\Object\Itf14` will automatically prepend
missing zeros to the barcode text.

### Zend\\Barcode\\Object\\Leitcode

![image](../images/zend.barcode.objects.details.leitcode.png)

This barcode extends `Zend\Barcode\Object\Identcode` (Deutsche Post Identcode), and inherits some of
its capabilities; it also has a few particulars of its own.

- **Name:** Leitcode (Deutsche Post Leitcode)
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10 different from Code25)
- **Length:** 14 characters (including checksum)

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 14, `Zend\Barcode\Object\Leitcode` will automatically
prepend missing zeros to the barcode text.

### Zend\\Barcode\\Object\\Planet

![image](../images/zend.barcode.objects.details.planet.png)

- **Name:** Planet (PostaL Alpha Numeric Encoding Technique)
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10)
- **Length:** 12 or 14 characters (including checksum)

There are no particular options for this barcode.

### Zend\\Barcode\\Object\\Postnet

![image](../images/zend.barcode.objects.details.postnet.png)

- **Name:** Postnet (POSTal Numeric Encoding Technique)
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10)
- **Length:** 6, 7, 10 or 12 characters (including checksum)

There are no particular options for this barcode.

### Zend\\Barcode\\Object\\Royalmail

![image](../images/zend.barcode.objects.details.royalmail.png)

- **Name:** Royal Mail or *RM4SCC* (Royal Mail 4-State Customer Code)
- **Allowed characters:**'0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
- **Checksum:** mandatory
- **Length:** variable

There are no particular options for this barcode.

### Zend\\Barcode\\Object\\Upca

![image](../images/zend.barcode.objects.details.upca.png)

This barcode extends `Zend\Barcode\Object\Ean13` (*EAN*-13), and inherits some of its capabilities;
it also has a few particulars of its own.

- **Name:** *UPC*-A (Universal Product Code)
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10)
- **Length:** 12 characters (including checksum)

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 12, `Zend\Barcode\Object\Upca` will automatically prepend
missing zeros to the barcode text.
The option `withQuietZones` has no effect with this barcode.

### Zend\\Barcode\\Object\\Upce

![image](../images/zend.barcode.objects.details.upce.png)

This barcode extends `Zend\Barcode\Object\Upca` (*UPC*-A), and inherits some of its capabilities; it
also has a few particulars of its own. The first character of the text to encode is the system (0 or
1).

- **Name:** *UPC*-E (Universal Product Code)
- **Allowed characters:**'0123456789'
- **Checksum:** mandatory (modulo 10)
- **Length:** 8 characters (including checksum)

There are no particular options for this barcode.

> ## Note
If the number of characters is lower than 8, `Zend\Barcode\Object\Upce` will automatically prepend
missing zeros to the barcode text.

> ## Note
If the first character of the text to encode is not 0 or 1, `Zend\Barcode\Object\Upce` will
automatically replace it by 0.
The option `withQuietZones` has no effect with this barcode.

