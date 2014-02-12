# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# Tag list
CREATE TABLE `{tag}` (
  `id`              int(10)               unsigned NOT NULL auto_increment,
  `term`            varchar(255)          NOT NULL default '',
  `count`           int(10)               unsigned NOT NULL default '0',

  PRIMARY KEY       (`id`),
  KEY `term`        (`term`)
);

# Tag-source link
CREATE TABLE `{link}` (
  `id`              int(10)                 unsigned NOT NULL auto_increment,
  `term`            varchar(255)            NOT NULL,
  `module`          varchar(64)             NOT NULL default '',
  `type`            varchar(64)             NOT NULL default '',
  `item`            int(10)                 unsigned NOT NULL default '0',
  `time`            int(10)                 unsigned NOT NULL default '0',
  `order`           int(10)                 unsigned NOT NULL default '0',

  PRIMARY KEY       (`id`),
  KEY `item`        (`module`, `type`, `item`),
  KEY `term`        (`term`)
);

# Stats per module-type
CREATE TABLE `{stats}` (
  `id`              int(10)                 unsigned NOT NULL auto_increment,
  `term`            varchar(255)            NOT NULL,
  `module`          varchar(64)             NOT NULL default '',
  `type`            varchar(64)             NOT NULL default '',
  `count`           int(10)                 unsigned NOT NULL default '0',

  PRIMARY KEY       (`id`),
  KEY `count`        (`module`, `type`, `count`)
);

# Placeholder for drafts
CREATE TABLE `{draft}` (
  `id`              int(10)                 unsigned NOT NULL auto_increment,
  `term`            varchar(255)            NOT NULL,
  `module`          varchar(64)             NOT NULL default '',
  `type`            varchar(64)             NOT NULL default '',
  `item`            int(10)                 unsigned NOT NULL default '0',
  `time`            int(10)                 unsigned NOT NULL default '0',
  `order`           int(10)                 unsigned NOT NULL default '0',

  PRIMARY KEY       (`id`),
  KEY `item`        (`module`, `type`, `item`),
  KEY `term`        (`term`)
);
