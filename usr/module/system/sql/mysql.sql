# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

-- Module categorization
CREATE TABLE `{category}` (
  `id`          int(10)         unsigned NOT NULL auto_increment,
  `title`       varchar(255)    default NULL,
  `icon`        varchar(255)    default '',
  `order`       int(5)          unsigned NOT NULL default '0',
  -- Json-encoded module list
  `modules`     text,

  PRIMARY KEY  (`id`)
);

-- Module update records
CREATE TABLE `{update}` (
  `id`          int(10)         unsigned NOT NULL auto_increment,
  `title`       varchar(255)    default NULL,
  `content`     text,
  `module`      varchar(64)     default NULL,
  `controller`  varchar(64)     default NULL,
  `action`      varchar(64)     default NULL,
  `route`       varchar(64)     default NULL,
  `params`      varchar(255)    default NULL,
  `uri`         varchar(255)    default NULL,
  `time`        int(10)         unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`)
);