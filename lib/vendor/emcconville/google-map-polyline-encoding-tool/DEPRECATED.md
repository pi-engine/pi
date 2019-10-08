# Deprecated Functionality

## Singleton

_Deprecated as of version 1.2.2. Removed in 2.0.0_

The Polyline object can be initialized as a single object, and be referenced
throughout an application.

```php
<?php
require_once 'Polyline.php';

// Create singleton
$myPolyline = Polyline::Singleton();

// Create a polyline from an array of points
$myPolyline->polyline("tribune",array(41.89084,-87.62386,41.89086,-87.62279
                                      41.89028,-87.62277,41.89028,-87.62385));

// Create a polyline from an encoded string
$myPolyline->polyline("dustygroove","kiw~FpoavObBA?fAzEC");

/* ... do work .. */

// Re-establish singleton object
$anotherPolyline = Polyline::Singleton();
var_dump( $anotherPolyline->getTribunePoints() );
var_dump( $anotherPolyline->getDustyGrooveEncoded() );

?>
```

Output:

```
string(22) "wxt~Fd`yuOCuErBC?vEoB@"

array(8) {
  [0] =>
  double(41.90374)
  [1] =>
  double(-87.66729)
  [2] =>
  double(41.90324)
  [3] =>
  double(-87.66728)
  [4] =>
  double(41.90324)
  [5] =>
  double(-87.66764)
  [6] =>
  double(41.90214)
  [7] =>
  double(-87.66762)
}
```

## Makefile

_Removed with 2.0.0_

Nobody uses `make` utility any more, and it did nothing useful but hold
shortcuts. Moving project-specific commands to [CONTRIBUTING](CONTRIBUTING.md)
document, and `.gitattributes`.
