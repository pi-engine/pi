CREATE TABLE `{tag}` (
  `id`              int(10)             unsigned NOT NULL auto_increment,
  `term`            varchar(255)        NOT NULL default '',
  `count`           int(10)             unsigned NOT NULL default '0',

  PRIMARY KEY (`id`)
);

CREATE TABLE `{link}` (
  `id`                  int(10)                 unsigned NOT NULL auto_increment,
  `tag`                 varchar(255)            NOT NULL,
  `module`              varchar(255)            NOT NULL default '',
  `type`                varchar(255)            NOT NULL default '',
  `item`                int(10)                 unsigned NOT NULL default '0',
  `time`                int(10)                 unsigned NOT NULL default '0',
  `order`               int(10)                 unsigned NOT NULL default '0',

  PRIMARY KEY              (`id`),
  KEY `tag`                (`tag`),
  KEY `item`               (`item`)
);

CREATE TABLE `{stats}` (
  `id`                 int(10)                unsigned NOT NULL auto_increment,
  `tag`                varchar(255)           NOT NULL,
  `module`             varchar(255)           NOT NULL default '',
  `type`               varchar(255)           NOT NULL default '',
  `count`              int(10)                unsigned NOT NULL default '0',

  PRIMARY KEY                (`id`),
  KEY `tag`                  (`tag`)
);