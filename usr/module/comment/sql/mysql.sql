# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# Comment
# >>>>

# Comment type
CREATE TABLE `{type}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `identifier`      varchar(64)     NOT NULL    default '',
  `params`          varchar(255)    NOT NULL    default '',
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  -- Callback to fetch source meta data
  `callback`        varchar(255)    NOT NULL    default '',
  -- Locator to identify root meta data
  `locator`         varchar(255)    NOT NULL    default '',
  `active`          tinyint(1)      unsigned    NOT NULL default '1',
  `icon`            varchar(255)    NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `module_type` (`module`, `name`)
);

# Comment root
CREATE TABLE `{root}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `module`          varchar(64)     NOT NULL,
  `type`            varchar(64)     NOT NULL    default '',
  `item`            int(10)         unsigned    NOT NULL,
  `active`          tinyint(1)      unsigned    NOT NULL default '1',
  -- User id of root item author
  `author`          int(10)         unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `module_item` (`module`, `type`, `item`),
  KEY `author` (`author`)
);

# Comment posts
CREATE TABLE `{post}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL default '0',
  `root`            int(10)         unsigned    NOT NULL,
  `reply`           int(10)         unsigned    NOT NULL default '0',
  `content`         text,
  -- Content markup: text, html, markdown
  `markup`          varchar(64)     NOT NULL    default '',
  `time`            int(10)         unsigned    NOT NULL default '0',
  `time_updated`    int(10)         unsigned    NOT NULL default '0',
  `active`          tinyint(1)      unsigned    NOT NULL default '1',
  `ip`              varchar(15)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL,

  PRIMARY KEY  (`id`),
  KEY  `uid` (`uid`),
  KEY  `root` (`root`)
);
