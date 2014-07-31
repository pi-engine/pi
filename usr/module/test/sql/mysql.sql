
CREATE TABLE `{user}` (
  `id`            int(10) unsigned        NOT NULL auto_increment,
  `username`         varchar(255)            NOT NULL default '',
  `phone`           varchar(255)          NOT NULL DEFAULT '',
  `email`           varchar(255)          NOT NULL DEFAULT '',
  `content`       text,
  `flag`          tinyint(1) unsigned     NOT NULL default '0',

  PRIMARY KEY  (`id`)
  );