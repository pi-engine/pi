Pi Locale
=========

SPECs
-----
* Top folder name as identifier for a language
* Folder name must be in lowercase
* Folder name, i.e. language tag must respect [RFC 4646](http://www.ietf.org/rfc/rfc4646.txt)
* Loacle files are in CSV format except mail templates which are in plain text
* Both keys and values in CSV must be quoted with double quote (```"```), delimited with comma (```,```)
* All files must be encoded in the same charset as Pi system, default as ```UTF-8```

Skeleton
--------

* ```usr/locale```: Global
  * ```/en```
    * ```main.csv```: Global, loaded on every request
    * ```navigation.csv```: Navigation and menu
    * ```timezone.csv```: Timezone
    * ...
  * ```/zh-cn```
    * ...
* ```usr/module/demo```: Module ```demo```
  * ```/en```
    * ```/mail```: Mail templates
      * ```mail-template.text```
    * ```main.csv```: module global, loaded on every request of current module
    * ```navigation.csv```
    * ```admin.csv```: Admin area
    * ```config.csv```: Config edit
    * ```feed.csv```: Feed
    * ...
  * ```/zh-cn```
    * ...
* ```usr/theme/default```: Theme ```default```
  * ```/en```
    * ```main.csv```: theme global
    * ```admin.csv```: Admin area
    * ...
  * ```/zh-cn```
    * ...
