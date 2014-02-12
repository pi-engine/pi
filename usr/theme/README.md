Pi Themes
=========


Folder and file skeleton
------------------------
* ```config.php```: required, defines meta data of a theme, following as configuration for default theme
```
    // Version
    'version'       => '1.0.0-beta.1',
    // Type of layouts available in the theme
    'type'          => 'both', // Potential value: 'both', 'admin', 'front', default as 'both'

    // Title of the theme
    'title'         => 'Pi Default Theme',
    // Author information: name, email, website
    'author'        => 'Theme architecture: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>; Resources: Pi Engine Development Team',
    // Screenshot image, relative path in asset. If no screenshot is available, static/image/screenshot.png will be used
    'screenshot'    => 'image/screenshot.png',
    // License or theme images and scripts
    'license'       => 'Creative Common License http://creativecommons.org/licenses/by/3.0/',
    // Optional description
    'description'   => 'Default theme for Pi Engine',
    // Parent theme from which templates can be inherited, default as 'default'
    'parent'        => '',
```
* ```README.md```: optional readme for a theme

* ```template/```: required for root theme, optional for children theme, for layout templates, error page templates, block component, paginator component, page-zone component, etc.
* ```asset/```: required for root theme, optional for children theme, for assets
  * ```css/```: required, stylesheet files
  * ```image/```: required, images used by a theme
  * ```js/```: optional, JavaScript files used by a theme
  * ```locale/```: optional, localization specific assets, like css, js or images
    * ```en/```
    * ```zh-cn/```
* ```locale/```: optional, locale data used by a theme
  * ```en/```
  * ```zh-cn/```
* ```module/```: on-demand, templates and assets extended from modules, for theme specific customization
  * ```demo/```: on-demand, module assets and templates
    * ```asset/```: on-demand, same skeleton as module/asset
    * ```template/```: on-demand, same skeleton as module/template


File Cheatsheet
---------------
* Templates REQUIRED for front:
  * tmplate/layout-front.phtml - complete layout template as component container: header, footer, body, blocks, navigation
  * tmplate/layout-simple.phtml - error page layout: header, footer, body
  * tmplate/layout-style.phtml - content with stylesheets
  * tmplate/layout-content.phtml - raw content without stylesheets
  * template/error.phtml - defined in var/config/config.application.php: view_manager.error_template
* Templates REQUIRED for admin:
  * tmplate/layout-admin.phtml - backoffice layout
* Templates OPTIONAL for front:
  * template/page-zone.phtml - for block manipulation on a page
  * template/block.phtml - called by layout-front.phtml
  * template/error-404.phtml - defined in var/config/config.application.php: view_manager.not_found_template
  * template/error-denied.phtml - defined in var/config/config.application.php: view_manager.denied_template
  * template/error-exception.phtml - defined in var/config/config.application.php: view_manager.exception_template
* Stylesheet file REQUIRED:
  * asset/css/style.css - main css file

Best Practices
--------------
* It is highly recommended to use 'pi-' as prefix for all global id and class names used in theme to avoid conflicts.
* It is highly recommended to use module identity as prefix for module id and class names used in templates to avoid conflicts, for instance 'demo-'.

Resources
---------
* [Pi Theme Repository](http://pialog.org/theme.html)
