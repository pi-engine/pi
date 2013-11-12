<?php
/* 
 * The configuration of simpleSAMLphp
 * 
 * $Id: config.php 3246 2013-05-23 11:43:52Z olavmrk $
 */

$config = array();
$customFile = Pi::path('config/custom/saml/config.php');
include $customFile;

$config = array_merge($config, array (

	'baseurlpath'           => 'sso/',

	'showerrors'            =>	FALSE,

	'timezone' => 'Asia/Shanghai',

	'store.type' => 'memcache',

	'memcache_store.servers' => array(
		array(
            array('hostname' => '192.168.18.14', 'port' => '11211'),
		),
	),

));
