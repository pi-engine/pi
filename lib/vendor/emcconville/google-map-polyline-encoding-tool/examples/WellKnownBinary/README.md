# Well-Known Binary

Vector geometry files can be standardized as a common text, or binary file.
Usually referenced as *well-known text* (`.wkt`), or *well-known binary* (`.wkb`).
This examples demonstrates reading `.wkb` files and outputting encoded strings.

***Notice:***
In this example; only Polygons are supported. Points, Lines, and MultiPolygons
can quickly be completed.

## Usage

```php
<?php
require 'src/Polyline.php';
require 'examples/WellKnownBinary/WkbPolyline.php';

$wkb = new WkbPolyline();
$encoded = $wkb->encodeFromFile( 'examples/WellKnownBinary/cleveland-mbr.wkb' );
//=> 'wz||Fr~vrN?_sbAhwh@??~rbAiwh@?'
$points = Polyline::decode($encoded);
//=> array(
//    41.60444, -81.87898, 41.60444, -81.53274,
//    41.39063, -81.53274, 41.39063, -81.87898,
//    41.60444, -81.87898
// )
```
![Cleveland Rocks][cleveland]

```php
// Or work directly with binary strings
$cleveland = "\x01\x03\x00\x00\x00\x01\x00\x00\x00\x05\x00\x00\x00\x35\x43\xaa"
           . "\x28\x5e\xcd\x44\x40\x02\x7e\x8d\x24\x41\x78\x54\xc0\x35\x43\xaa"
           . "\x28\x5e\xcd\x44\x40\xf9\x48\x4a\x7a\x18\x62\x54\xc0\x71\x73\x2a"
           . "\x19\x00\xb2\x44\x40\xf9\x48\x4a\x7a\x18\x62\x54\xc0\x71\x73\x2a"
           . "\x19\x00\xb2\x44\x40\x02\x7e\x8d\x24\x41\x78\x54\xc0\x35\x43\xaa"
           . "\x28\x5e\xcd\x44\x40\x02\x7e\x8d\x24\x41\x78\x54\xc0";
$encoded = $wkb->encodeFromBlob( $cleveland );
//=> 'wz||Fr~vrN?_sbAhwh@??~rbAiwh@?'
```

[cleveland]: http://emcconville.com/Polyline/cleveland.png