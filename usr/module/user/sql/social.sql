# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User custom compound
# >>>>

# Entity for user custom compound: social tools
CREATE TABLE `{social}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  `order`           smallint(5)     unsigned    NOT NULL default '0',

  `type`            varchar(64)     NOT NULL default '',
  `title`           varchar(64)     NOT NULL,
  `identifier`      varchar(64)     NOT NULL,

  PRIMARY KEY  (`id`),
  KEY  `uid` (`uid`)
);
