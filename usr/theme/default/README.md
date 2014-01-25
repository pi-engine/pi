Default Theme
=============

Pi Engine default theme




**Folder and file skeleton**

* Templates REQUIRED for front:
  *  tmplate/layout-front.phtml - complete layout template: header, footer, body, blocks, navigation
  *  tmplate/layout-simple.phtml - error page layout: header, footer, body
  *  tmplate/layout-style.phtml - content with stylesheets
  *  tmplate/layout-content.phtml - raw content without stylesheets
  *  template/error.phtml - defined in var/config/config.application.php: view_manager.error_template
* Templates REQUIRED for admin:
  *  tmplate/layout-admin.phtml - backoffice layout
* Templates OPTIONAL for front:
  *  template/page-zone.phtml - for block manipulation on a page
  *  template/block.phtml - called by layout-front.phtml
  *  template/error-404.phtml - defined in var/config/config.application.php: view_manager.not_found_template
  *  template/error-denied.phtml - defined in var/config/config.application.php: view_manager.denied_template
  *  template/error-exception.phtml - defined in var/config/config.application.php: view_manager.exception_template
* Stylesheet file REQUIRED:
*  asset/css/style.css - main css file

**Best practices**
*  It is highly recommended to use 'pi-' as prefix for all global id and class names used in theme to avoid conflicts.
*  It is highly recommended to use module identity as prefix for module id and class names used in templates to avoid conflicts, for instance 'demo-'.



**Block zones**

```
 |<- lt ->|<------------------        center        ------------------>|<- rt ->|


 --------------------------------------------------------------------------------
 |                                                                              |
 |                                        0                                     |
 |                                                                              |
 --------------------------------------------------------------------------------
 |        |                                                            |        |
 |        |                               2                            |        |
 |        |                                                            |        |
 |        |------------------------------------------------------------|        |
 |        |                               |                            |        |
 |        |                3              |             4              |        |
 |        |                               |                            |        |
 |   1    |------------------------------------------------------------|   8    |
 |        |                                                            |        |
 |        |                             module                         |        |
 |        |                                                            |        |
 |        |------------------------------------------------------------|        |
 |        |                               |                            |        |
 |        |                5              |             6              |        |
 |        |                               |                            |        |
 |        |------------------------------------------------------------|        |
 |        |                                                            |        |
 |        |                               7                            |        |
 |        |                                                            |        |
 |--------|------------------------------------------------------------|--------|
 |                                                                              |
 |                                        99                                    |
 |                                                                              |
 --------------------------------------------------------------------------------

 |<- lt ->|<------------------        center        ------------------>|<- rt ->|
```

### front.css
```
1. Bootstrap custom css or [custom bootstrap.css](http://getbootstrap.com/customize/) in theme
1. Block
   * head
     * pi-panel-subline
     * tab
   * body
1. ellipsis
   * .pi-ellipsis
   * .pi-ellipsis-1
   * .pi-ellipsis-2
   * .pi-ellipsis-3
   * .pi-ellipsis-4
1. pi-list
1. pi-order-list
1. pi-footer
1. module-custom

```