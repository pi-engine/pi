# Quote all table names with '{' and '}', and prefix all system tables with 'core.'
CREATE TABLE `{widget}` (
  `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `block` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `name`  VARCHAR(64)      NOT NULL DEFAULT '',
  `type`  VARCHAR(64)      NOT NULL DEFAULT '',
  `meta`  TEXT,

  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `block` (`block`)
);