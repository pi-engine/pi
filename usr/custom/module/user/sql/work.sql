# Pi Engine schema
# http://pialog.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User custom compound
# >>>>

# Entity for user custom compound: work experiences
CREATE TABLE `{work}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL,
  `order`           smallint(5)     unsigned    NOT NULL default '0',

  `company`         varchar(64)     NOT NULL,
  `industry`        varchar(64)     NOT NULL,
  `sector`          varchar(64)     NOT NULL,
  `department`      varchar(64)     NOT NULL,
  `position`        varchar(64)     NOT NULL,
  `title`           varchar(64)     NOT NULL,
  `start`           varchar(64)     NOT NULL,
  `end`             varchar(64)     NOT NULL,
  `description`     text,

  PRIMARY KEY  (`id`),
  KEY  `uid` (`uid`)
);
