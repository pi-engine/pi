# Pi Engine schema
# http://piengine.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# ------------------------------------------------------
# User custom compound
# >>>>

# Entity for user custom compound: social tools
CREATE TABLE `{social}` (
  `id`         INT(10) UNSIGNED     NOT NULL    AUTO_INCREMENT,
  `uid`        INT(10) UNSIGNED     NOT NULL,
  `order`      SMALLINT(5) UNSIGNED NOT NULL    DEFAULT '0',

  `type`       VARCHAR(64)          NOT NULL    DEFAULT '',
  `title`      VARCHAR(64)          NOT NULL,
  `identifier` VARCHAR(64)          NOT NULL,

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
);
