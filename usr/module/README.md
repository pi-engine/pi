Pi Modules
==========


Folder and file skeleton
------------------------
* ```module.php```: required, defines meta data, author data and resources of a module, following as configuration for system module
```
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => __('System'),
        // Description, for admin, optional
        'description'   => __('For administration of core functions of the site.'),
        // Version number, required
        'version'       => '3.2.1',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Readme file, for admin, optional
        'readme'        => 'docs/readme.txt',
        // Direct download link, available for wget, optional
        //'download'      => 'http://dl.xoopsengine.org/core',
        // Demo site link, optional
        'demo'          => 'http://demo.xoopsengine.org/demo',

        // Module is ready for clone? Default as false
        'clonable'      => false,
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'name'      => 'Taiwen Jiang',
        // Email address, optional
        'email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'website'   => 'http://www.xoopsengine.org',
        // Credits and aknowledgement, optional
        'credits'   => 'Pi Engine Team; Zend Framework Team; EEFOCUS Team.'
    ),
    // Maintenance actions
    'maintenance'   => array(
        // resource
        'resource' => array(
            // Database meta
            'database'  => array(
                // SQL schema/data file
                'sqlfile'   => 'sql/mysql.sql',
            ),
            // System config
            'config'    => 'config.php',
            // ACL specs
            'acl'       => 'acl.php',
            // Block definition
            'block'     => 'block.php',
            // Event specs
            'event'     => 'event.php',
            // View pages
            'page'      => 'page.php',
            // Navigation definition
            'navigation'    => 'navigation.php',
            // Routes, first in last out; bigger priority earlier out
            'route'     => 'route.php',
        )
    )
```
* ```README.md```: optional readme for a module

* ```asset/```: optional, for assets
  * ```image/```: optional, images used by a module, it's recommended to add a logo.png file as module logo
  * ```script/```: optional, JavaScript files or CSS files used by a module
* ```config/```: required, configuration files used by a module
  * ```module.php```: required, defines module meta data, author information and resources needed
  * ```config.php```: optional, defines module configuration
  * ```navigation.php```: optional, defaines front-end and backend navigation of a module
* ```locale/```: optional, localization specific assets
  * ```en/```
    * ```main.csv```: optional, default localization file should be named `main` and its format is `csv`
  * ```zh-cn/```
* ```sql/```: optional, contains sql file
  * ```mysql.sql```: optional, contains MySQL query for initializing module tables
* ```src/```: required, core files of a module
  * ```Api/```: optional, API classes for other module
  * ```Controller/```: required, controller classes used by a module
    * ```Admin/```: optional, backend controllers
	* ```Front/```: optional, front-end controllers
  * ```Form/```: optional, form classes used by a module
  * ```Installer/```: optional, custom setup classes used by a module
    * ```Action/```: optional, custom module setup
	* ```Resource/```: optional, custom resources setup
  * ```Model/```: optional, extend database model used by a module
  * ```Route/```: optional, custom route used by a module
  * ```Registry/```: optional, custom registry used by a module
  * ```Block.php```: optional, block controllers used by a module
* ```template/```: optional, template files used by a module
  * ```admin/```: optional, backend template files
  * ```front/```: optional, front-end template files
* ```doc/```: required, docs for users to understand the module
  * ```changelog.md```: module changelog, as clear as possible
  * ```releasenote.md```: module release note
  * ```manifest.md```: development design of the module, please reference this: [Module Workflow](https://github.com/pi-engine/pi/wiki/Dev.Module-Workflow)
* ```README.md```


Resources
---------
* [Pi Module Repository](http://pialog.org/module.html)
* [Module Development Documentation](https://github.com/pi-engine/pi/wiki/Pi-Documentation-Team)
* [Coding Standards](https://github.com/pi-engine/pi/wiki/Dev.Coding-Standards)
* [Database Schema](https://github.com/pi-engine/pi/wiki/Dev.Database-Schema)
* [Code Review](https://github.com/pi-engine/pi/wiki/Dev.Code-Review)
* [Pi Security](https://github.com/pi-engine/pi/wiki/Dev.Security)
