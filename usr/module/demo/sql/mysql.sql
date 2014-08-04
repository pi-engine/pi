# Quote all table names with '{' and '}', and prefix all system tables with 'core.'

CREATE TABLE `{test}` (
  `id`            int(10) unsigned        NOT NULL auto_increment,
  `message`       varchar(255)            NOT NULL default '',
  `active`        tinyint(1)              NOT NULL default '1',

  PRIMARY KEY  (`id`)
);

CREATE TABLE `{page}` (
  `id`            int(10) unsigned        NOT NULL auto_increment,
  `title`         varchar(255)            NOT NULL default '',
  `uid`           int(10)         unsigned    NOT NULL default '0',
  `time_created`  int(10)         unsigned    NOT NULL default '0',
  `content`       text,
  `flag`          tinyint(1) unsigned     NOT NULL default '0',

  PRIMARY KEY  (`id`)
);