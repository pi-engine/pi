Pi Engine 2.2.0 Release
=======================

The Pi Team is pleased to present on Summer Day the Pi 2.2.0 Release (or Summer Day Release), a role oriented application development engine based on Zend Framwork 2.

Get the [Pi Summer Day Release](https://github.com/pi-engine/pi/archive/release-2.2.0.zip) and report issues and requests to [Pi Issue Tracker](https://github.com/pi-engine/pi/issues).

What's New
==========
During the months of intensive development, a lot of **[features](https://github.com/pi-engine/pi/blob/release-2.2.0/doc/changelog.txt)** have been introduced or improved with dedicated **[documentation](https://github.com/pi-engine/pi/wiki)**. Among of those exciting features, the One-Deployment-Multiple-Instance feature is demonstrated with three websites: Pi Engine Dialogue ([pialog.org](http://pialog.org) | [pialogue.org](http://pialogue.org)), Pi Engine Demo ([pi-demo.org](http://pi-demo.org)) and Xoops/Pi Demo ([demo.xoopsengine.org](http://demo.xoopsengine.org)).

Some of the major changes:
* Third-party updated: Zend Framework 2.2.1; Bootstrap 1.0.0; Backbone 2.3.1; MakeItUp 1.1.14
* MVC/View strategy cleaned up and Pi components aligned for compatibility with ZF components
* Services introduced: mail service, application audit service, user service
* L10n support introduced and locale folder name file format canonized
* HTML head meta and title rendering improved for theme templates
* Filesystem cache mechanism improved for convenient APIs
* Variable filtering/sanitizing improved with convenient syntactic sugar
* Theme inheritance introduced and module template inheritance improved
* Module operation process improved with responsive messages
* Module and front-end development documented massively
* Config files:
   + Added: var/config/config.application.php
   + Added: var/config/service.user.php
   + Added: var/config/service.audit.php
   + Added: var/config/service.mail.php
   + Added: var/config/service.security.php
   + Modified: var/config/service.markup.php
   + Modified: var/config/resource.security.php
   + Modified: var/config/application.front.php
   + Modified: var/config/application.admin.php
   + Modified: var/config/application.feed.php

Check out [changelog](https://github.com/pi-engine/pi/blob/release-2.2.0/doc/changelog.txt) for details from @taiwen, @sexnothing, @voltan, @MarcoXoops, @linzongshu, @kris and etc.

Tutorials
=========
Aside from framework and module developments, **[tutorials](https://github.com/pi-engine/pi/wiki)** are being built up at Pi Wiki and a variety of development teams start to learn and build their applications on Pi Engine, for instance @linzongshu, @sexnothing and @9sheng with their teams respectively.


---------------------
Taiwen Jiang, Pi Team 

June 22nd, 2013 
