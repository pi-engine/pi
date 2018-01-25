# Pi Engine schema
# http://piengine.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

-- Module categorization
CREATE TABLE `{category}` (
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`   VARCHAR(255)              DEFAULT NULL,
  `icon`    VARCHAR(255)              DEFAULT '',
  `order`   INT(5) UNSIGNED  NOT NULL DEFAULT '0',
  -- Json-encoded module list
  `modules` TEXT,

  PRIMARY KEY (`id`)
);

-- Module update records
CREATE TABLE `{update}` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255)              DEFAULT NULL,
  `content`    TEXT,
  `module`     VARCHAR(64)               DEFAULT NULL,
  `controller` VARCHAR(64)               DEFAULT NULL,
  `action`     VARCHAR(64)               DEFAULT NULL,
  `route`      VARCHAR(64)               DEFAULT NULL,
  `params`     VARCHAR(255)              DEFAULT NULL,
  `uri`        VARCHAR(255)              DEFAULT NULL,
  `time`       INT(10) UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`)
);