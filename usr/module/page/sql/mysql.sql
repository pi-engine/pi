# Quote all table names with '{' and '}', and prefix all system tables with 'core.'

CREATE TABLE `{page}` (
  `id`              int(10) unsigned        NOT NULL auto_increment,
  `title`           varchar(255)            NOT NULL default '',
  `name`            varchar(64)             default NULL,
  `user`            int(10)                 unsigned    NOT NULL default '0',
  `time_created`    int(10)                 unsigned    NOT NULL default '0',
  `time_updated`    int(10)                 unsigned    NOT NULL default '0',
  `active`          tinyint(1)              NOT NULL default '1',
  `content`         text,
  `markup`          varchar(64)             NOT NULL default 'text',
  `slug`            varchar(64)             default NULL,
  `clicks`          int(10)                 unsigned    NOT NULL default '0',
 
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
);

# NOT used yet. Solely for demonstration, will be dropped
CREATE TABLE `{stats}` (
  `id`      int(10) unsigned        NOT NULL auto_increment,
  `page`    int(10)                 unsigned    NOT NULL default '0',
  `clicks`  int(10)                 unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `page` (`page`)
);