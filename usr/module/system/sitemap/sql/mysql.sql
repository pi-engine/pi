CREATE TABLE `{url_list}` ( 
  `id` int(10) unsigned NOT NULL auto_increment,
  `loc` varchar(255) NOT NULL default '',
  `lastmod` varchar(64) NOT NULL default '',
  `changefreq` varchar(64) NOT NULL default '',
  `priority` varchar(64) NOT NULL default '',
  `time_create` int(10) unsigned NOT NULL default '0',
  `module` varchar(64) NOT NULL default '',
  `table` varchar(64) NOT NULL default '',
  `item` int(10) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '0',
  `top` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `loc` (`loc`),
  KEY `status` (`status`),
  KEY `time_create` (`time_create`),
  KEY `module` (`module`),
  KEY `table` (`table`),
  KEY `item` (`item`),
  KEY `top` (`top`),
  KEY `create_id` (`id`, `time_create`, `status`),
  KEY `module_table` (`module`, `table`)
);

CREATE TABLE `{generate}` ( 
  `id` int(10) unsigned NOT NULL auto_increment,
  `file` varchar(64) NOT NULL default '',
  `time_create` int(10) unsigned NOT NULL default '0',
  `time_update` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `end` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `file` (`file`),
  KEY `time_create` (`time_create`)
);