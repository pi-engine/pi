# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User custom compound
# >>>>

# Entity for user custom compound: location
CREATE TABLE `{location}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  `order`           smallint(5)     unsigned    NOT NULL default '0',

  `country`         varchar(64)     NOT NULL,
  `province`        varchar(64)     NOT NULL,
  `city`            varchar(64)     NOT NULL,

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `uid` (`uid`)
);
