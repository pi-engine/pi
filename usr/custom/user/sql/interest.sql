# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User custom compound
# >>>>

# Entity for user custom compound: interest
CREATE TABLE `{interest}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  `order`           smallint(5)     unsigned    NOT NULL default '0',

  `value`           varchar(64)     NOT NULL,

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `uid` (`uid`, `value`)
);
