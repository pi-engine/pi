# Contributing

Thanks for helping [google-map-polyline-encoding-tool][l1].

## Project Goal

This library exists as a PHP reference point for Google's
[Encoded Polyline Algorithm Format][ref]. Any improvements in
documentation & performance are very welcome. Ideally, this library will be
reduced to basic encode/decode functions, and superseded by other
[advanced & elegant][l2] libraries.

Review the following items before creating a pull-request.

### Please Do


Ensure that code-coverage meets, or beats, previous tag.


    $ vendor/bin/phpunit --coverage-text

Run PHPCS's lint on affected files.

    $ vendor/bin/phpcs {examples,src,tests}

_Happy Hacking!_


 [ref]: http://code.google.com/apis/maps/documentation/utilities/polylinealgorithm.html
 [l1]: https://github.com/emcconville/google-map-polyline-encoding-tool
 [l2]: https://github.com/emcconville/polyline-encoder