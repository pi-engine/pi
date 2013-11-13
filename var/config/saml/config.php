<?php
/* 
 * The configuration of simpleSAMLphp
 * 
 * $Id: config.php 3246 2013-05-23 11:43:52Z olavmrk $
 */

$customFile = Pi::path('config/custom/saml/config.php');
if (is_readable($customFile)) {
    include $customFile;
    return;
}

include __DIR__ . '/config.default.php';
return;