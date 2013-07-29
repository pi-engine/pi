# Quote all table names with '{' and '}', and prefix all system tables with 'core.'

CREATE TABLE `{page}` (
  `id`              int(10) unsigned        NOT NULL auto_increment,
  `title`           varchar(255)            NOT NULL default '',
  `user`            int(10)                 unsigned    NOT NULL default '0',
  `time_created`    int(10)                 unsigned    NOT NULL default '0',
  `time_updated`    int(10)                 unsigned    NOT NULL default '0',
  `active`          tinyint(1)              NOT NULL default '1',
  `content`         text,
  `markup`          varchar(64)             NOT NULL default 'text',
  `slug`            varchar(64)             default NULL,
  `clicks`          int(10)                 unsigned    NOT NULL default '0',
  `script`          text,
  `style`           text,
  `seo_keywords`    varchar(255)            NOT NULL,
  `seo_description` varchar(255)            NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `title` (`title`),
  KEY `time_created` (`time_created`),
  KEY `active` (`active`)
);