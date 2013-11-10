Pi Locale
=========

SPECs
-----
* Top folder name as identifier for a language
* Folder name must be in lowercase
* Folder name, i.e. language tag must respect [RFC 4646](http://www.ietf.org/rfc/rfc4646.txt)
* Locale files are in po/mo format except mail templates which are in plain text
* All files must be encoded in the same charset as Pi system, default as `UTF-8`

Skeleton
--------

* `usr/locale`: Global
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
* `usr/theme/default`: Theme `default`
  * `/en`
    * `default.mo`: theme global
    * `admin.mo`: Admin area
    * ...
  * `/zh-cn`
    * ...

Use `poedit` to extract module language items:
* _a(), t() => admin.mo
* _b() => block.mo
* __(), _e() and others => default.mo
