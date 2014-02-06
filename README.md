Pi Engine
=================

Pi Engine is a role oriented application development engine for web and mobile, designed as the next generation and a successor to Xoops.
Pi is developed upon PHP and MySQL with selected third-party frameworks including but not limited to [Zend Framework 2](https://github.com/zendframework/zf2), jQuery, Bootstrap, Angular and Backbone.

Pi Project follows the philosophy of open standard, open design, open development and open management. Pi is born as a complete open source project and intended to build a sustainable ecosystem that benefits all contributors and users. 

Pi Engine is developed by [Pi Team](https://github.com/pi-engine/pi/wiki/Pi-Team) initially as a successor to Xoops led by Ono Kazumi (onokazu), skalpa and Taiwen Jiang (phppp or D.J.) successively since 2001.

**Check out [Latest Release](https://github.com/pi-engine/pi/blob/master/doc/releasenotes.md).**


Highlights
-------------
1. **Sustainable ecosystem:** A sustainable ecosystem built upon open standard, open source code, open development and open management on Github.
2. **Engineered development:** Quality ensured engineering development with short learning curve, low skill requirements with clean MVC architecture, semantic templating, sophisticated API and strict starndards.
3. **Visualized management:** Easy and responsive application and content management based on visualized mangement tools and interface with page and widget mechanism.
4. **Agile workflow:** Role oriented architecture and deployment skeleton supports manageable agile development workflow.

Features and practices
----------------------
* Modularization for functionality and applications
* Components for basic libraries and services for fundamental system functions
* Theming for presentation and appearance
* Design-friendly templating
* DevOps oriented architecture
* Centralized security enhancement

Quick start
-----------
* Documents at [Pi Wiki](https://github.com/pi-engine/pi/wiki) and [APIs](http://api.pialog.org).
* Download the [latest stable code](https://github.com/pi-engine/pi/zipball/master) and [latest dev code](https://github.com/pi-engine/pi/zipball/develop).
* Clone Pi repo `git clone git://github.com/pi-engine/pi.git`.
* Resources: [Pi modules](https://github.com/pi-module), [Pi themes](https://github.com/pi-theme) and [extensions] (https://github.com/pi-engine/pi/blob/master/README-GIT.md).

Development
----------

You may contribute to Pi Engine by [working on Pi code](https://github.com/pi-engine/pi/blob/master/README-GIT.md) and submit to Pi repo with **[Pull Request](https://help.github.com/articles/using-pull-requests)** or submitting bug reports and feature requests to **[Issue Tracker](https://github.com/pi-engine/pi/issues)**.


Copyright and License
---------------------

The Engine is released under a [New BSD License](https://github.com/pi-engine/pi/blob/master/doc/license.txt).


Demos
-----
Demo sites with shared deployment: 
* Pi Dialogue ([pialog.org](http://pialog.org) | [pialogue.org](http://pialogue.org))
* Pi Demo ([pi-demo.org](http://pi-demo.org))
* Xoops/Pi ([xoopsengine.org](http://demo.xoopsengine.org))

Front-end building asset
----------------
Tools: [gruntjs](http://gruntjs.com/)

To execute asset build, use:

```
grunt 
```

To execute clear asset build files, use

```
grunt clear
```

To execute 'www/asset' and 'www/public' files to usr, use

```
grunt back
```

