Pi Engine 2.2.2 Release
=======================

The Pi Team is pleased to present Pi Engine 2.2.2 with a huge mount of updates on structure and functionality along Zend Framwork update to 2.2.2.

Get the [Pi 2.2.2 Release](https://github.com/pi-engine/pi/archive/release-2.2.2.zip) and report issues and requests to [Pi Issue Tracker](https://github.com/pi-engine/pi/issues).

What's New
==========
During the month of intensive development, the Pi Engine framework is improved significantly on standards and completeness of APIs with **[features](https://github.com/pi-engine/pi/blob/release-2.2.2/doc/changelog.txt)**.
Documentation is continuously improved and the Pi development [API site](http://api.pialog.org) is generated with phpdoc.

Some of the major changes:
* Significant improvements on documentation including front end manual on github wiki
* PHP/CSS/HTML/JavaScript coding standards are confirmed and Pi PHP docblocks are validated with phpdoc
* Identified coding standards and formulated major APIs for modules and themes
* Refactored Default theme and rebuilt Pi theme as strict HTML 5 support
* Refactored blocks by adding a head zone and a foot zone
* Added complete user service APIs
* Added extra path and Extra namespace to allow customization for modules
* Formulated view strategy with Json/Feed/Api data model and view model
* Added new sugar syntactic _t() for translation and _scape()/_strip() for text processing
* Forced prefix "pi-" for Pi specific class and id names for CSS/JavaScript and HTML
* Config files:
   * + Modified: var/config/config.application.php
   * - Removed: var/config/service.authentication.php

Check out [changelog](https://github.com/pi-engine/pi/blob/release-2.2.2/doc/changelog.txt) for details.

Documentation
=============
Checkout Pi development manual and tutorials at [Github wiki](https://github.com/pi-engine/pi/wiki) and APIs and class charts at [Pi API](http://api.pialog.org).


---------------------
[Taiwen Jiang](http://github.com/taiwen), [Pi Team](http://pi-engine.org)

August 3rd, 2013
