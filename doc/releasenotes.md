Pi Engine 2.4.0 Release
=======================

The Pi Team is pleased to present the Pi Engine **Pi Day** Release (Pi 2.4.0), an application development engine focusing on user oriented architecture, feature and API building. Meanwhile Pi powered applications are launched by a variety of teams.

Get the [Pi Day Release](https://github.com/pi-engine/pi/archive/release-2.4.0.zip) and report issues and requests to [Pi Issue Tracker](https://github.com/pi-engine/pi/issues).

What's New
==========

During these months’ development, some features have been added and improved to Pi Engine and its basic modules.

Some of the major changes:

+ New basic modules available: `media`, `search`
+ Refactored API calls to be compatible with registry and model calls: changed `Pi::api(<module>)` and `Pi::api(<module>, <api>)` to `Pi::api(<api>, <module>)`
+ Media service introduced to operate media in local or remote
+ Added security and permission checks
+ Added all modules updating features
+ Added built-in support for module custom with build metadata versioning
+ Added built-in support for module and theme custom asset
+ Added customizable breadcrumbs provides by module itself
+ Simplified config API with `Pi::config()`
+ Merged module `asset` and `public`, located in `www/asset`; independent asset deployment dropped off
+ Moved custom static to `asset/custom` folder for better deployment
+ Moved system abstract API classes to sub namespace `Api` located in `lib/Pi/Application/Api`
+ Added support for custom bootstrap and online custom bootstrap in themes
+ Added service of `string` for multi-byte string handling
+ Upgraded Zend Framework to 2.2.6 final
+ Upgraded Bootstrap to 3.1.1
+ Improvements and bugs fixed on core module: `user`, `page`, `article`, `comment`, `message`, `tag`, `search`, `saml` and `uclient`
+ Added config folders/files:
  + var/config/event.listener.php
+ Modified config files:
  + var/config/engine.php
  + var/config/host.php
  + var/config/hosts.php

Check out [changelog](https://github.com/pi-engine/pi/blob/release-2.4.0/doc/changelog.txt) for details. And finally special thank goes to @[sexnothing](https://github.com/sexnothing), @[Simon Zhang](https://github.com/zhangsimon) and @[loidco](https://github.com/loidco).

Documentation
=============
Checkout Pi development manual and tutorials at [Github wiki](https://github.com/pi-engine/pi/wiki) and APIs and class charts at [Pi API](http://api.pialog.org).


---------------------
[Taiwen Jiang](http://github.com/taiwen), [Marc Desrousseaux](https://github.com/Marc-pi), [Hossein Azizabadi](http://github.com/voltan), [Zongshu Lin](https://github.com/linzongshu), [Pi Team](http://pi-engine.org)

Mar 14th, 2014