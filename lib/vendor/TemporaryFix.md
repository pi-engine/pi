Some fix on during upgrade process
=================
* Do it for php 7.2 and 7.3
* Not test on php 7.4

1- Zend Session ( Zend\Session )
-------------
* 1-1- Fix `src/AbstractContainer.php`
  * change `offsetGet` to `&offsetGet` (Add `&` before offsetGet)
* 1-2- Fix `src/Config/SessionConfig.php` 
  * from : https://github.com/zendframework/zend-session/issues/104
  * hack : https://github.com/zendframework/zend-session/pull/107/files#diff-e2854706102e7ee4d11218f33886fc9a
  
2- Zend DB ( Zend\Db )
-------------
* 2-1- Fix `src/Metadata/Source/AbstractSource.php` 
  * from : https://github.com/zendframework/zend-db/pull/276/files
* 2-2- Fix `src/Sql/AbstractSql.php` 
  * from : https://github.com/zendframework/zend-db/pull/276/files
