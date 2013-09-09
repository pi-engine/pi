# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# Comment
# >>>>

# Comment category
CREATE TABLE `{category}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `module`          varchar(64)     NOT NULL default '',
  `controller`      varchar(64)     NOT NULL default '',
  `action`          varchar(64)     NOT NULL default '',
  `identifier`      varchar(64)     NOT NULL default '',
  `params`          varchar(255)    NOT NULL default '',
  `name`            varchar(64)     NOT NULL default '',
  `title`           varchar(255)    NOT NULL default '',
  `callback`        varchar(255)    NOT NULL default '',
  `active`          tinyint(1)      unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `module_category` (`module`, `name`)
);

# Comment root
CREATE TABLE `{root}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `module`          varchar(64)     NOT NULL default '',
  `category`        varchar(64)     NOT NULL default '',
  `item`            int(10)         unsigned    NOT NULL,
  `active`          tinyint(1)      unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `module_item` (`module`, `category`, `item`)
);

# Comment posts
CREATE TABLE `{post}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  `root`            int(10)         unsigned    NOT NULL,
  `content`         text,
  `time`            int(10)         unsigned    NOT NULL default '0',
  `time_updated`    int(10)         unsigned    NOT NULL default '0',
  `active`          tinyint(1)      unsigned NOT NULL default '0',
  'ip'              varchar(15)     NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY  `uid` (`uid`),
  KEY  `root` (`root`)
);
