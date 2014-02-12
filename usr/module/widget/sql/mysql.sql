# Quote all table names with '{' and '}', and prefix all system tables with 'core.'

CREATE TABLE `{widget}` (
  `id`              int(10) unsigned NOT NULL auto_increment,
  `block`           int(10) unsigned NOT NULL default '0',
  `time`            int(10) unsigned NOT NULL default '0',
  `name`            varchar(64) NOT NULL default '',
  `type`            varchar(64) NOT NULL default '',
  `meta`            text,

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `block` (`block`)
);