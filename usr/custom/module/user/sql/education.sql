# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User custom compound
# >>>>

# Entity for user custom compound: education
CREATE TABLE `{education}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  `order`           smallint(5)     unsigned    NOT NULL default '0',

  `school`          varchar(64)     NOT NULL,
  `department`      varchar(64)     NOT NULL,
  `major`           varchar(64)     NOT NULL,
  `degree`          varchar(64)     NOT NULL,
  `start`           varchar(64)     NOT NULL,
  `end`             varchar(64)     NOT NULL,
  `description`     text,

  PRIMARY KEY  (`id`),
  KEY  `uid` (`uid`)
);
