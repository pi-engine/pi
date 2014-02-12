# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# Search
# >>>>

# Search log
CREATE TABLE `{log}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `term`            varchar(255)    NOT NULL    default '',
  `uid`             int(10)         unsigned    NOT NULL default '0',
  `time`            int(10)         unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`)
);

# Search auto complete
CREATE TABLE `{dictionary}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `term`            varchar(255)    NOT NULL    default '',
  `weight`          int(10)         unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  KEY `weight` (`weight`)
);
