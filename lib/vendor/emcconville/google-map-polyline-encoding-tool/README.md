# Google Maps Polyline Encoding Tool

[![Build Status](https://travis-ci.org/emcconville/google-map-polyline-encoding-tool.svg?branch=master)](https://travis-ci.org/emcconville/google-map-polyline-encoding-tool)
[![Coverage Status](https://img.shields.io/coveralls/emcconville/google-map-polyline-encoding-tool.svg)](https://coveralls.io/r/emcconville/google-map-polyline-encoding-tool?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/emcconville/google-map-polyline-encoding-tool.svg)](https://packagist.org/packages/emcconville/google-map-polyline-encoding-tool)

A simple PHP class for translating [polyline][polylineRef] into an 
[encoded][algorithmRef] strings for Google Maps.

## Install

Use [composer][composer].

```
$ curl -sS https://getcomposer.org/installer | php
$ cat > composer.json <<EOF
{
   "require": {
      "emcconville/google-map-polyline-encoding-tool" : ">=1.2.1"
   }
}
EOF
$ php composer.phar install
```

Old fashion way.

```
$ git clone git://github.com/emcconville/google-map-polyline-encoding-tool.git
$ cp src/Polyline.php /path/to/your/application/includes/Polyline.php
```

## Usage

### Encoding

```php
// Points to encode
$points = array(
        array(41.89084,-87.62386),
        array(41.89086,-87.62279),
        array(41.89028,-87.62277),
        array(41.89028,-87.62385),
        array(41.89084,-87.62386)
    );

$encoded = Polyline::encode($points);
//=> wxt~Fd`yuOCuErBC?vEoB@
```

![Tribune][tribuneTower]

### Decoding

```php
// String to decode
$encoded = "kiw~FpoavObBA?fAzEC";

$points = Polyline::decode($encoded);
//=> array(
//     41.90374,-87.66729,41.90324,-87.66728,
//     41.90324,-87.66764,41.90214,-87.66762
//   );

// Or list of tuples
$points = Polyline::pair($points);
//=> array(
//     array(41.90374,-87.66729),
//     array(41.90324,-87.66728),
//     array(41.90324,-87.66764),
//     array(41.90214,-87.66762)
//   );
```

![Records][dustyGroove]

## Examples

See [examples](examples) directory for creative ideas, and please contribute new
use-cases / hacks.

 - [Well-Known Binary](examples/WellKnownBinary) converter.
 - Preview encoded strings with a [SVG transcoder](examples/EncodedToSVG).

### Specify precision

Precision defaults to 1e-5 (0.00001) which is expected by Google Map API. Other 
API's like [OSRM][osrmRef] expect a precision of 1e-6.
You can adjust the precision you want by sub-classing Polyline, and overwrite
the `$precision` static property.

```php
class PolylineOSRM extends Polyline
{
    protected static $precision = 6;
}
$points = PolylineOSRM::decode($line);
$line = PolylineOSRM::encode($points);
```

**Caution**

 - Adjusting the precision level will not guarantee improved accuracy. Existing
   issues with PHP's internal float point arithmetic can contribute accuracy issues.
 - Third party libraries will not automatically know what level of precision was
   used during encoding.


## Family

This library exists as a PHP reference point for Google's 
[Encoded Polyline Algorithm Format][algorithmRef]. There is also a 
[C implementation][l3], and a [namespace/trait library][l2] under active 
development.

|Requires|[google-map-polyline-encoding-tool][l1]|[polyline-encoder][l2]|[php_polyline][l3]|
|:-------|:-------------------------------------:|:--------------------:|:----------------:|
|PHP     | 5.3                                   | 5.4                  | C-API            |

|Supports|[google-map-polyline-encoding-tool][l1]|[polyline-encoder][l2]|[php_polyline][l3]|
|:-------|:-------------------------------------:|:--------------------:|:----------------:|
|Google  |&#10004;                               |&#10004;              |&#10004;          |
|Bing    |                                       |&#10004;              |                  |
|Precision|&#10004;                              |&#10004;              |&#10004;          |
|Tuple   |                                       |&#10004;              |&#10004;          |
|Traits  |                                       |&#10004;              |                  |
|Abstraction|                                    |&#10004;              |                  |


[polylineRef]: http://code.google.com/apis/maps/documentation/javascript/reference.html#Polygon
[algorithmRef]: http://code.google.com/apis/maps/documentation/utilities/polylinealgorithm.html
[tribuneTower]: http://emcconville.com/Polyline/tribune.png
[dustyGroove]: http://emcconville.com/Polyline/dustygroove.png
[osrmRef]: http://map.project-osrm.org/
[l1]: https://github.com/emcconville/google-map-polyline-encoding-tool
[l2]: https://github.com/emcconville/polyline-encoder
[l3]: https://github.com/emcconville/php_polyline
[composer]: https://github.com/composer/composer