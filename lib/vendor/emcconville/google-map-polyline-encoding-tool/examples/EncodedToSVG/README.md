# Encoded to SVG

What if you just want a quick SVG image from a encoded string? In this example,
we can decode a string, calculate the MBR, and pass the data to a SVG document.


```php
<?php
require 'src/Polyline.php';
require 'examples/EncodedToSVG/EncodedToSVG.php';

$encoded = $_GET['encoded'];

$svg = EncodedToSVG::decodeToSVG($encoded);

header('Content-Type: image/svg+xml');
print $svg;
```

![Cleveland](http://emcconville.com/Polyline/cleveland.svg)