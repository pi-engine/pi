Pi Locale
=========

SPECs
-----

* Top folder name as identifier for a language
* Folder name must be in lowercase
* Folder name, i.e. language tag must respect [RFC 4646](http://www.ietf.org/rfc/rfc4646.txt)
* Locale files are in po/mo format except mail templates which are in plain text
* All files must be encoded in the same charset as Pi system, default as `UTF-8`


i18n APIs
--------

* `__()` => `Pi::service('i18n')->translate()`
* `_a()` => `Pi::service('i18n')->translate()`
* `_b()` => `Pi::service('i18n')->translate()`
* `_e()` => `echo Pi::service('i18n')->translate()`
* `_t()` => NULL

API calls
--------

* To be collected in `admin.mo`
  - `config/config.php`: `_t()`
  - `config/event.php`: `_t()`
  - `config/permission.php`: `_t()`
  - `config/navigation.php` [admin], [meta]: `_t()`
  - `config/*` [others]: `_a()`
  - `src/Controller/Admin/*`: `_a()`
  - `src/Installer/*`: `_a()`
  - `template/admin/*`: `_a()`
* To be collected in `block.mo`
  - `src/Block/*`: `_b()`
  - `template/block/*`: `_b()`
* To be collected in `default.mo`
  - All other files: `__()` or `_e()`


Skeleton
--------

* `usr/locale`: Global
  * `/en`
    * `default.mo`: Global, loaded on every request
    * `timezone.mo`: Timezone
    * ...
  * `/zh-cn`
    * ...
  * `/_source`: placeholder for specified translations
* `usr/custom/locale`: Global custom locale
  * `/en`
    * `default.mo`: Global, loaded on every request
    * `timezone.mo`: Timezone
    * ...
  * `/zh-cn`
    * ...

* `usr/module/system`: Module `system`
  * `/en`
    * `default.mo`: module global, loaded on every request of current module
    * `admin.mo`: Admin area
    * `block.mo`: Module block
    * ...
  * `/zh-cn`
    * ...
* `usr/module/demo`: Module `demo`
  * `/en`
    * `/mail`: Mail templates
      * `mail-template.text`
    * `default.mo`: module global, loaded on every request of current module
    * `admin.mo`: Admin area
    * `block.mo`: Module block
    * ...
  * `/zh-cn`
    * ...
* `usr/custom/module/demo`: Module `demo` custom
  * `/en`
    * `/mail`: Mail templates
      * `mail-template.text`
    * `default.mo`: module global, loaded on every request of current module
    * `admin.mo`: Admin area
    * `block.mo`: Module block
    * ...
  * `/zh-cn`
    * ...
* `usr/theme/default`: Theme `default`
  * `/en`
    * `default.mo`: theme global
    * ...
  * `/zh-cn`
    * ...

Tools
-----

* `Poedit`
  * Download: http://www.poedit.net/download.php

* Use `poedit` to extract module language items:
  * `_a()`, `t()` => `admin.mo`
  * `_b()` => `block.mo`
  * `__()`, `_e()` and others => `default.mo`
