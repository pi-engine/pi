Some fix on during upgrade process
=================
* Do it for php 7.2 and 7.3
* Not test on php 7.4

1- Zend Session ( Zend\Session )
-------------
* 1-1- Fix `src/AbstractContainer.php`
  * change `offsetGet` to `&offsetGet` (Add `&` before offsetGet)
* 1-2- Fix ` src/Config/SessionConfig.php` 
  * from : https://github.com/zendframework/zend-session/issues/104
  * hack : https://github.com/zendframework/zend-session/pull/107/files#diff-e2854706102e7ee4d11218f33886fc9a