# Pi Engine schema
# http://piengine.org
# Author: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
# --------------------------------------------------------

# Tag list
CREATE TABLE `{tag}` (
  `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `term`  VARCHAR(255)     NOT NULL DEFAULT '',
  `count` INT(10) UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `term`        (`term`)
);

# Tag-source link
CREATE TABLE `{link}` (
  `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `term`   VARCHAR(255)     NOT NULL,
  `module` VARCHAR(64)      NOT NULL DEFAULT '',
  `type`   VARCHAR(64)      NOT NULL DEFAULT '',
  `item`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `order`  INT(10) UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `item`        (`module`, `type`, `item`),
  KEY `term`        (`term`)
);

# Stats per module-type
CREATE TABLE `{stats}` (
  `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `term`   VARCHAR(255)     NOT NULL,
  `module` VARCHAR(64)      NOT NULL DEFAULT '',
  `type`   VARCHAR(64)      NOT NULL DEFAULT '',
  `count`  INT(10) UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `count`        (`module`, `type`, `count`)
);

# Placeholder for drafts
CREATE TABLE `{draft}` (
  `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `term`   VARCHAR(255)     NOT NULL,
  `module` VARCHAR(64)      NOT NULL DEFAULT '',
  `type`   VARCHAR(64)      NOT NULL DEFAULT '',
  `item`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `order`  INT(10) UNSIGNED NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `item`        (`module`, `type`, `item`),
  KEY `term`        (`term`)
);
